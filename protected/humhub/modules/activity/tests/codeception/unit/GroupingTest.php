<?php

namespace activity\unit;

use Codeception\Specify;
use humhub\modules\activity\components\BaseActivity;
use humhub\modules\activity\controllers\ActivityBoxController;
use humhub\modules\activity\models\Activity;
use humhub\modules\activity\services\ActivityManager;
use humhub\modules\activity\tests\codeception\activities\TestActivity;
use humhub\modules\activity\tests\codeception\activities\TestContentGroupActivity;
use humhub\modules\comment\models\Comment;
use humhub\modules\content\interfaces\ContentProvider;
use humhub\modules\content\models\Content;
use humhub\modules\like\activities\LikeActivity;
use humhub\modules\like\services\LikeService;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\db\Expression;

class GroupingTest extends HumHubDbTestCase
{
    use Specify;

    public function testRemoveGroup()
    {
        $this->becomeUser('User2');
        $post = Post::findOne(['id' => 1]);

        ActivityManager::dispatch(TestActivity::class, $post);

        $a1 = ActivityManager::dispatch(TestContentGroupActivity::class, $post);
        $a2 = ActivityManager::dispatch(TestContentGroupActivity::class, $post);
        $a3 = ActivityManager::dispatch(TestContentGroupActivity::class, $post);
        $a4 = ActivityManager::dispatch(TestContentGroupActivity::class, $post);
        $a5 = ActivityManager::dispatch(TestContentGroupActivity::class, $post);

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

    public function testAddToGroup()
    {
        $this->becomeUser('User2');
        $post = Post::findOne(['id' => 1]);

        // Some other activity
        ActivityManager::dispatch(TestActivity::class, $post);

        $a1 = ActivityManager::dispatch(TestContentGroupActivity::class, $post);
        $a2 = ActivityManager::dispatch(TestContentGroupActivity::class, $post);
        $a3 = ActivityManager::dispatch(TestContentGroupActivity::class, $post);

        // Check is still ungrouped
        $a1->record->refresh();
        $a3->record->refresh();
        $this->assertNotEquals($a1->record->grouping_key, $a3->record->grouping_key);

        // Check now in group
        $a4 = ActivityManager::dispatch(TestContentGroupActivity::class, $post);
        $a1->record->refresh();
        $a3->record->refresh();

        $this->assertEquals($a1->record->grouping_key, $a3->record->grouping_key);
        $this->assertEquals($a4->record->grouping_key, $a1->record->grouping_key);

        $a5 = ActivityManager::dispatch(TestContentGroupActivity::class, $post);
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
        $this->assertEquals(3, ActivityBoxController::getQuery($space->contentContainerRecord)->count());

        $this->becomeUser('User2');
        $post3 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'C']);
        $post3->save();
        $post4 = new Post($space, Content::VISIBILITY_PUBLIC, ['message' => 'D']);
        $post4->save();

        $this->becomeUser('User1');
        $query = ActivityBoxController::getQuery($space->contentContainerRecord);
        $this->assertEquals(2, $query->count());

        $activity = ActivityManager::load($query->one());
        $this->assertEquals('Sara Tester created a new post "D" and 3 more.', $activity->asText());
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
            $this->getActivityForContent($post)->asText(),
        );

        $this->becomeUser('Admin'); // Admin
        $this->assertEquals(
            'Andreas Tester and Sara Tester like post "Hello World!".',
            $this->getActivityForContent($post)->asText(),
        );

        $this->becomeUser('User1'); // Peter Tester
        (new LikeService($post))->like();

        $this->becomeUser('Admin'); // Admin
        $this->assertEquals(
            'Peter Tester, Andreas Tester and 1 more like post "Hello World!".',
            $this->getActivityForContent($post)->asText(),
        );

        $this->becomeUser('User1'); // Peter Tester
        $this->assertEquals(
            'Andreas Tester and Sara Tester like post "Hello World!".',
            $this->getActivityForContent($post)->asText(),
        );
        (new LikeService($post))->unlike();
        $this->assertEquals(
            'Andreas Tester and Sara Tester like post "Hello World!".',
            $this->getActivityForContent($post)->asText(),
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
            $this->getActivityForContent($post, LikeActivity::class)->asText(),
        );

        $this->becomeUser('User1'); // Peter Tester
        (new LikeService($comment))->like();

        $this->becomeUser('Admin');
        $this->assertEquals(
            'Peter Tester and Sara Tester like comment "Test comment!".',
            $this->getActivityForContent($post, LikeActivity::class)->asText(),
        );

        $this->becomeUser('User3'); // Andreas Tester
        (new LikeService($comment))->like();

        $this->becomeUser('Admin');
        $this->assertEquals(
            'Andreas Tester, Peter Tester and 1 more like comment "Test comment!".',
            $this->getActivityForContent($post, LikeActivity::class)->asText(),
        );

        $this->becomeUser('User1'); // Peter Tester
        (new LikeService($comment))->unlike();

        $this->becomeUser('Admin');
        $this->assertEquals(
            'Andreas Tester and Sara Tester like comment "Test comment!".',
            $this->getActivityForContent($post, LikeActivity::class)->asText(),
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
        $this->assertEquals(1, ActivityBoxController::getQuery($space->contentContainerRecord)->count());

        // Destroy Group
        $post1->move($space2, true);

        $this->becomeUser('User1');
        $this->assertEquals(3, ActivityBoxController::getQuery($space->contentContainerRecord)->count());

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
        $this->assertEquals(2, ActivityBoxController::getQuery($space->contentContainerRecord)->count());

        $post1->content->visibility = Content::VISIBILITY_PRIVATE;
        $post1->content->save();

        $this->assertEquals(3, ActivityBoxController::getQuery($space->contentContainerRecord)->count());

        $post2->content->visibility = Content::VISIBILITY_PRIVATE;
        $post2->content->save();

        $this->assertEquals(4, ActivityBoxController::getQuery($space->contentContainerRecord)->count());

        $post3->content->visibility = Content::VISIBILITY_PRIVATE;
        $post3->content->save();

        // Now expect 2 groups
        $this->assertEquals(2, ActivityBoxController::getQuery($space->contentContainerRecord)->count());
    }

    private function getActivityForContent(ContentProvider $record, ?string $activityClass = null): ?BaseActivity
    {
        $query = ActivityBoxController::getQuery($record->content->contentContainer)->andWhere(
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
