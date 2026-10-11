<?php

namespace activity\unit;

use Codeception\Specify;
use humhub\modules\activity\components\BaseActivity;
use humhub\modules\activity\models\Activity;
use humhub\modules\activity\services\ActivityListService;
use humhub\modules\activity\services\ActivityManager;
use humhub\modules\activity\tests\codeception\activities\TestActivity;
use humhub\modules\activity\tests\codeception\activities\TestContentGroupActivity;
use humhub\modules\activity\tests\codeception\activities\TestDeclaredGroupingActivity;
use humhub\modules\activity\tests\codeception\activities\TestGroupedNamesActivity;
use humhub\modules\activity\tests\codeception\activities\TestLegacyMemberAddedActivity;
use humhub\modules\comment\models\Comment;
use humhub\modules\content\activities\ContentCreatedActivity;
use humhub\modules\content\interfaces\ContentProvider;
use humhub\modules\content\models\Content;
use humhub\modules\like\activities\LikeActivity;
use humhub\modules\like\services\LikeService;
use humhub\modules\notification\components\Grouping;
use humhub\modules\post\models\Post;
use humhub\modules\space\activities\MemberAddedActivity;
use humhub\modules\space\activities\MemberRemovedActivity;
use humhub\modules\space\models\Space;
use humhub\modules\user\activities\FollowActivity;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Expression;

class GroupingTest extends HumHubDbTestCase
{
    use Specify;

    protected function tearDown(): void
    {
        TestDeclaredGroupingActivity::$grouping = null;
        Yii::$container->clear(ContentCreatedActivity::class);
        parent::tearDown();
    }

    public function testRemoveGroup()
    {
        $this->becomeUser('User2');
        $post = Post::findOne(['id' => 1]);

        TestActivity::send($post);

        $a1 = TestContentGroupActivity::send($post);
        $a2 = TestContentGroupActivity::send($post);
        $a3 = TestContentGroupActivity::send($post);
        $a4 = TestContentGroupActivity::send($post);
        $a5 = TestContentGroupActivity::send($post);

        $a1->record->refresh();
        $this->assertEquals(
            5,
            Activity::find()->andWhere(['activity.grouping_key' => $a1->record->grouping_key])->count(),
        );

        $a4->record->refresh();
        $a4->record->delete();
        $a2->record->refresh();
        $a2->record->delete();

        $this->assertEquals(
            3,
            Activity::find()->andWhere(['activity.grouping_key' => $a1->record->grouping_key])->count(),
        );

        $a3->record->refresh();
        $a3->record->delete();

        // Group Destroyed
        $this->assertEquals(
            1,
            Activity::find()->andWhere(['activity.grouping_key' => $a1->record->grouping_key])->count(),
        );
    }

    public function testGroupedUsersAreDistinctAndExcludeTheNamedAuthor()
    {
        $post = Post::findOne(['id' => 1]);

        // Two users, two activities each: the entry's own author therefore has a second
        // activity in the group besides the one representing it.
        $this->becomeUser('User2');
        TestContentGroupActivity::send($post);
        TestContentGroupActivity::send($post);

        $this->becomeUser('User3');
        TestContentGroupActivity::send($post);
        $latest = TestContentGroupActivity::send($post);
        $latest->record->refresh();

        $entry = ActivityManager::load($latest->record);
        $others = $entry->getGroupingService()->getOtherGroupedUsers();
        $names = array_map(static fn($user) => $user->displayName, $others);

        // The sentence names the entry's own author first and the others after it. An author
        // with a second activity in the group must not turn up among those others - that is
        // what rendered as "Sara Schuster and Sara Schuster joined the space".
        $this->assertNotContains($entry->user->displayName, $names);
        // And a user with several activities is one person, named once.
        $this->assertSame(array_values(array_unique($names)), $names);
        $this->assertCount(1, $others);
    }

    public function testAGroupOfOnePersonStillNamesThem()
    {
        $post = Post::findOne(['id' => 1]);

        // Two activities of the same user: a group without anyone else in it.
        $this->becomeUser('User2');
        TestGroupedNamesActivity::send($post);
        TestGroupedNamesActivity::send($post);

        // Read it the way the activity list does - `group_count` comes from the grouped
        // select, a plain record does not carry it.
        $record = Activity::find()
            ->enableGrouping()
            ->andWhere(['activity.class' => TestGroupedNamesActivity::class])
            ->one();
        $entry = ActivityManager::load($record);

        $this->assertGreaterThan(1, $entry->groupCount, 'the activities did group');
        // A grouped message renders `{displayNames}` and nothing else, so the phrase must
        // name the one person rather than come back empty ("joined the Space test.").
        $this->assertStringContainsString($entry->user->displayName, $entry->asWeb());
    }

    public function testAddToGroup()
    {
        $this->becomeUser('User2');
        $post = Post::findOne(['id' => 1]);

        // Some other activity
        TestActivity::send($post);

        $a1 = TestContentGroupActivity::send($post);
        $a2 = TestContentGroupActivity::send($post);
        $a3 = TestContentGroupActivity::send($post);

        // Check is still ungrouped
        $a1->record->refresh();
        $a3->record->refresh();
        $this->assertNotEquals($a1->record->grouping_key, $a3->record->grouping_key);

        // Check now in group
        $a4 = TestContentGroupActivity::send($post);
        $a1->record->refresh();
        $a3->record->refresh();

        $this->assertEquals($a1->record->grouping_key, $a3->record->grouping_key);
        $this->assertEquals($a4->record->grouping_key, $a1->record->grouping_key);

        $a5 = TestContentGroupActivity::send($post);
        $a1->record->refresh();
        $this->assertEquals($a1->record->grouping_key, $a5->record->grouping_key);

        // Check group of 5
        $this->assertEquals(
            5,
            Activity::find()->andWhere(['activity.grouping_key' => $a1->record->grouping_key])->count(),
        );
    }

    public function testMultiplePostGrouping()
    {
        $this->becomeUser('User2');
        $space = Space::findOne(['id' => 2]);

        $post0 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => '0']);
        $post0->save();
        Activity::updateAll(
            ['created_at' => new Expression('created_at - INTERVAL 2 HOUR')],
            ['content_id' => $post0->content->id],
        );

        $post1 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'A']);
        $post1->save();

        $post2 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'B']);
        $post2->save();

        $this->becomeUser('User1');
        $this->assertEquals(3, ActivityListService::query($space->contentContainerRecord)->count());

        $this->becomeUser('User2');
        $post3 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'C']);
        $post3->save();
        $post4 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'D']);
        $post4->save();

        $this->becomeUser('User1');
        $query = ActivityListService::query($space->contentContainerRecord);
        $this->assertEquals(2, $query->count());

        $activity = ActivityManager::load($query->one());
        $this->assertEquals('Sara Tester created a new post "D" and 3 more.', $activity->asMailText());
    }

    public function testLikeGrouping()
    {
        $this->becomeUser('Admin');
        $space = Space::findOne(['id' => 2]);
        $post = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Hello World!']);
        $post->save();

        $this->becomeUser('User2'); // Sara Tester
        (new LikeService($post))->like();

        $this->becomeUser('User3'); // Andreas Tester
        (new LikeService($post))->like();

        $this->assertEquals(
            'Sara Tester likes post "Hello World!".',
            $this->getActivityForContent($post)->asMailText(),
        );

        $this->becomeUser('Admin'); // Admin
        $this->assertEquals(
            'Andreas Tester and Sara Tester like post "Hello World!".',
            $this->getActivityForContent($post)->asMailText(),
        );

        $this->becomeUser('User1'); // Peter Tester
        (new LikeService($post))->like();

        $this->becomeUser('Admin'); // Admin
        $this->assertEquals(
            'Peter Tester, Andreas Tester and 1 more like post "Hello World!".',
            $this->getActivityForContent($post)->asMailText(),
        );

        $this->becomeUser('User1'); // Peter Tester
        $this->assertEquals(
            'Andreas Tester and Sara Tester like post "Hello World!".',
            $this->getActivityForContent($post)->asMailText(),
        );
        (new LikeService($post))->unlike();
        $this->assertEquals(
            'Andreas Tester and Sara Tester like post "Hello World!".',
            $this->getActivityForContent($post)->asMailText(),
        );
    }

    public function testLikeComment()
    {
        $this->becomeUser('Admin');
        $space = Space::findOne(['id' => 2]);

        $post = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'Hello World!']);
        $post->save();

        $comment = new Comment([
            'message' => 'Test comment!',
            'content_id' => $post->content->id,
        ]);
        $comment->save();

        $this->becomeUser('User2'); // Sara Tester
        (new LikeService($comment))->like();


        $this->becomeUser('Admin');
        $this->assertEquals(
            'Sara Tester likes comment "Test comment!".',
            $this->getActivityForContent($post, LikeActivity::class)->asMailText(),
        );

        $this->becomeUser('User1'); // Peter Tester
        (new LikeService($comment))->like();

        $this->becomeUser('Admin');
        $this->assertEquals(
            'Peter Tester and Sara Tester like comment "Test comment!".',
            $this->getActivityForContent($post, LikeActivity::class)->asMailText(),
        );

        $this->becomeUser('User3'); // Andreas Tester
        (new LikeService($comment))->like();

        $this->becomeUser('Admin');
        $this->assertEquals(
            'Andreas Tester, Peter Tester and 1 more like comment "Test comment!".',
            $this->getActivityForContent($post, LikeActivity::class)->asMailText(),
        );

        $this->becomeUser('User1'); // Peter Tester
        (new LikeService($comment))->unlike();

        $this->becomeUser('Admin');
        $this->assertEquals(
            'Andreas Tester and Sara Tester like comment "Test comment!".',
            $this->getActivityForContent($post, LikeActivity::class)->asMailText(),
        );
    }

    public function testChangeContentContainer()
    {
        $this->becomeUser('Admin');
        $space = Space::findOne(['id' => 2]);
        $space2 = Space::findOne(['id' => 1]);

        ($post1 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'A']))->save();
        ($post2 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'B']))->save();
        ($post3 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'C']))->save();
        ($post4 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'C']))->save();

        $this->becomeUser('User1');
        $this->assertEquals(1, ActivityListService::query($space->contentContainerRecord)->count());

        // Destroy Group
        $post1->move($space2, true);

        $this->becomeUser('User1');
        $this->assertEquals(3, ActivityListService::query($space->contentContainerRecord)->count());

    }

    public function testChangeVisibility()
    {
        $this->becomeUser('Admin');
        $space = Space::findOne(['id' => 2]);

        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'A']))->save();
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'B']))->save();
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'C']))->save();
        ($post1 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'D']))->save();
        ($post2 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'E']))->save();
        ($post3 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'F']))->save();
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'F']))->save();
        (new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'F']))->save();

        $postA = new Post($space, Content::VISIBILITY_PRIVATE, ['message' => 'A']);
        $postA->save();

        $this->becomeUser('User1');
        $this->assertEquals(2, ActivityListService::query($space->contentContainerRecord)->count());

        $post1->content->visibility = Content::VISIBILITY_PRIVATE;
        $post1->content->save();

        $this->assertEquals(3, ActivityListService::query($space->contentContainerRecord)->count());

        $post2->content->visibility = Content::VISIBILITY_PRIVATE;
        $post2->content->save();

        $this->assertEquals(4, ActivityListService::query($space->contentContainerRecord)->count());

        $post3->content->visibility = Content::VISIBILITY_PRIVATE;
        $post3->content->save();

        // Now expect 2 groups
        $this->assertEquals(2, ActivityListService::query($space->contentContainerRecord)->count());
    }

    public function testCoreGroupings()
    {
        $this->assertEquals(
            Grouping::byClass()->andContainer()->andOriginator()->andContentType()->andContentVisibility()->withThreshold(4),
            ContentCreatedActivity::grouping(),
        );
        $this->assertEquals(Grouping::byContent()->andSourceParent(), LikeActivity::grouping());
        $this->assertEquals(Grouping::byContainer(), FollowActivity::grouping());
        $this->assertEquals(Grouping::byContainer(), MemberAddedActivity::grouping());
        $this->assertEquals(Grouping::byContainer(), MemberRemovedActivity::grouping());
    }

    public function testFollowsOfOneUserAreGrouped()
    {
        $followed = User::findOne(['id' => 1]);
        $other = User::findOne(['id' => 4]);

        $a1 = FollowActivity::send($followed, User::findOne(['id' => 2]));
        $b1 = FollowActivity::send($other, User::findOne(['id' => 2]));
        $this->assertNotSame($this->groupingKey($a1), $this->groupingKey($b1), 'not grouped below the threshold');

        $a2 = FollowActivity::send($followed, User::findOne(['id' => 3]));
        $this->assertSame($this->groupingKey($a1), $this->groupingKey($a2), 'the follows of one user');
        $this->assertNotSame($this->groupingKey($a1), $this->groupingKey($b1), 'the follow of another user');
    }

    public function testMembershipsOfOneSpaceAreGroupedPerClass()
    {
        $this->becomeUser('Admin');
        $space = Space::findOne(['id' => 1]);

        $a1 = MemberAddedActivity::send($space, User::findOne(['id' => 2]));
        $b1 = MemberAddedActivity::send(Space::findOne(['id' => 2]), User::findOne(['id' => 2]));
        $r1 = MemberRemovedActivity::send($space, User::findOne(['id' => 4]));
        $a2 = MemberAddedActivity::send($space, User::findOne(['id' => 3]));
        $r2 = MemberRemovedActivity::send($space, User::findOne(['id' => 5]));

        $this->assertSame($this->groupingKey($a1), $this->groupingKey($a2));
        $this->assertSame($this->groupingKey($r1), $this->groupingKey($r2));
        $this->assertNotSame($this->groupingKey($a1), $this->groupingKey($b1), 'another space');
        $this->assertNotSame($this->groupingKey($a1), $this->groupingKey($r1), 'another class');
    }

    public function testLikesOfTheContentAndOfItsCommentsAreSeparateGroups()
    {
        $this->becomeUser('Admin');
        $post = new Post(Space::findOne(['id' => 2]), Content::VISIBILITY_PUBLIC, ['message' => 'Hello World!']);
        $this->assertTrue($post->save());
        $comment1 = new Comment(['message' => 'One', 'content_id' => $post->content->id]);
        $this->assertTrue($comment1->save());
        $comment2 = new Comment(['message' => 'Two', 'content_id' => $post->content->id]);
        $this->assertTrue($comment2->save());

        foreach (['User1', 'User2', 'User3'] as $user) {
            $this->becomeUser($user);
            (new LikeService($post))->like();
            (new LikeService($comment1))->like();
        }
        $this->becomeUser('User1');
        (new LikeService($comment2))->like();

        $sizes = array_map(
            static fn(Activity $record) => (int)$record->group_count,
            Activity::find()->enableGrouping()
                ->andWhere(['activity.class' => LikeActivity::class, 'activity.content_id' => $post->content->id])
                ->all(),
        );
        sort($sizes);

        // the post, the first comment, the second comment
        $this->assertSame([1, 3, 3], $sizes);
    }

    public function testContentTypesExcludedFromGroupingAreNotGrouped()
    {
        Yii::$container->set(ContentCreatedActivity::class, ['contentTypeNoGrouping' => [Post::class]]);

        $this->becomeUser('Admin');
        $space = Space::findOne(['id' => 2]);
        foreach (['A', 'B', 'C', 'D'] as $message) {
            $this->assertTrue((new Post($space, Content::VISIBILITY_PUBLIC, ['message' => $message]))->save());
        }

        $this->becomeUser('User1');
        $this->assertEquals(4, ActivityListService::query($space->contentContainerRecord)->count());
    }

    public function testDeclaredGroupingUsesItsThresholdAndTimeBucket()
    {
        TestDeclaredGroupingActivity::$grouping = Grouping::byContent()->withThreshold(3);
        $this->becomeUser('User2');
        $post = Post::findOne(['id' => 1]);

        $old = TestDeclaredGroupingActivity::send($post);
        Activity::updateAll(
            ['created_at' => new Expression('created_at - INTERVAL 1 HOUR')],
            ['id' => $old->record->id],
        );

        $a1 = TestDeclaredGroupingActivity::send($post);
        $a2 = TestDeclaredGroupingActivity::send($post);
        $this->assertNotSame($this->groupingKey($a1), $this->groupingKey($a2), 'below the threshold of the grouping');

        // not the groupingThreshold (10) of the class
        $a3 = TestDeclaredGroupingActivity::send($post);
        $this->assertSame($this->groupingKey($a1), $this->groupingKey($a3));
        $this->assertSame($this->groupingKey($a2), $this->groupingKey($a3));
        $this->assertNotSame($this->groupingKey($old), $this->groupingKey($a3), 'another time bucket');
    }

    public function testByContentRequiresAContent()
    {
        TestDeclaredGroupingActivity::$grouping = Grouping::byContent();
        $space = Space::findOne(['id' => 1]);

        $a1 = TestDeclaredGroupingActivity::send($space, User::findOne(['id' => 2]));
        $a2 = TestDeclaredGroupingActivity::send($space, User::findOne(['id' => 3]));

        $this->assertNotSame($this->groupingKey($a1), $this->groupingKey($a2));
    }

    public function testRejectsUnseenOnly()
    {
        TestDeclaredGroupingActivity::$grouping = Grouping::byClass()->unseenOnly();

        $this->expectException(InvalidConfigException::class);
        TestDeclaredGroupingActivity::send(Space::findOne(['id' => 1]), User::findOne(['id' => 2]));
    }

    public function testAnOverrideOfTheDeprecatedGroupingQueryWins()
    {
        $space = Space::findOne(['id' => 1]);

        $a1 = TestLegacyMemberAddedActivity::send($space, User::findOne(['id' => 2]));
        $a2 = TestLegacyMemberAddedActivity::send($space, User::findOne(['id' => 3]));

        $this->assertNotSame($this->groupingKey($a1), $this->groupingKey($a2));
    }

    private function groupingKey(BaseActivity $activity): string
    {
        $activity->record->refresh();

        return (string)$activity->record->grouping_key;
    }

    private function getActivityForContent(ContentProvider $record, ?string $activityClass = null): ?BaseActivity
    {
        $query = ActivityListService::query($record->content->contentContainer)->andWhere(
            ['content_id' => $record->content->id],
        );

        if ($activityClass !== null) {
            $query->andWhere(['activity.class' => $activityClass]);
        }
        $activityRecord = $query->one();

        if ($activityRecord !== null) {
            return ActivityManager::load($activityRecord);
        }


        return null;
    }

}
