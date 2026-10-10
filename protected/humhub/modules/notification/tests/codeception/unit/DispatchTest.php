<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use humhub\components\db\AfterCommit;
use humhub\modules\admin\notifications\ExcludeGroupNotification;
use humhub\modules\admin\notifications\IncludeGroupNotification;
use humhub\modules\admin\notifications\NewVersionAvailableNotification;
use humhub\modules\comment\models\Comment;
use humhub\modules\comment\notifications\NewCommentNotification;
use humhub\modules\content\notifications\ContentCreatedNotification;
use humhub\modules\friendship\notifications\FriendshipRequestNotification;
use humhub\modules\like\notifications\NewLikeNotification;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\components\NotificationPriority;
use humhub\modules\notification\events\BeforeDispatchEvent;
use humhub\modules\notification\events\UnreadCountChangedEvent;
use humhub\modules\notification\jobs\DispatchJob;
use humhub\modules\notification\models\Notification;
use humhub\modules\notification\services\NotificationSettingsService;
use humhub\modules\notification\targets\MailTarget;
use humhub\modules\notification\targets\WebTarget;
use humhub\modules\notification\tests\codeception\unit\notifications\TestContentNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestGroupedNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestHighPriorityNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestModuleCategoryNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestRejectingNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestThrowingNotification;
use humhub\modules\post\models\Post;
use humhub\modules\space\models\Space;
use humhub\modules\space\notifications\SpaceInviteNotification;
use humhub\modules\space\notifications\SpaceNotificationTrait;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\User;
use humhub\modules\user\notifications\FollowedNotification;
use humhub\modules\user\notifications\MentionedNotification;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\Event;
use yii\base\InvalidArgumentException;
use yii\log\Logger;
use yii\queue\PushEvent;
use yii\queue\Queue;

/**
 * The queue of the test application is synchronous, so {@see BaseNotification::send()}
 * runs the {@see DispatchJob} inline.
 */
class DispatchTest extends HumHubDbTestCase
{
    public $fixtureConfig = ['default'];

    public function testRejectsAClassThatIsNoNotification()
    {
        $this->expectException(InvalidArgumentException::class);
        NotificationManager::dispatch(Post::class, [1]);
    }

    public function testDispatchWritesOneRecordPerRecipientAndSkipsTheOriginator()
    {
        // post 2: public post on the admin's profile, visible to all recipients
        $post = Post::findOne(['id' => 2]);
        TestContentNotification::send([1, 2, 3], $post, User::findOne(['id' => 2]));

        $rows = $this->rows(TestContentNotification::class);
        $this->assertSame([1, 3], array_map(fn(Notification $n) => (int)$n->user_id, $rows));
        foreach ($rows as $row) {
            $this->assertSame($post->content->id, (int)$row->content_id);
            $this->assertSame($post->content->contentcontainer_id, (int)$row->contentcontainer_id);
            $this->assertSame(NotificationPriority::Normal->value, (int)$row->priority);
            $this->assertSame(1, (int)$row->listed);
            $this->assertSame(2, (int)$row->originator_id);
            $this->assertNull($row->source_record_id);
            $this->assertNull($row->seen_at);
            $this->assertSame((int)$row->id, (int)$row->grouping_key);
        }
    }

    public function testNotifyOriginatorOption()
    {
        TestContentNotification::send([1, 2], Post::findOne(['id' => 2]), User::findOne(['id' => 2]), notifyOriginator: true);
        $this->assertSame([1, 2], $this->recipientIds(TestContentNotification::class));
    }

    public function testPriorityIsThatOfTheClass()
    {
        TestNotification::send(1);
        $this->assertSame(NotificationPriority::Low->value, (int)$this->rows(TestNotification::class)[0]->priority);

        TestHighPriorityNotification::send(1);
        $this->assertSame(NotificationPriority::High->value, (int)$this->rows(TestHighPriorityNotification::class)[0]->priority);
    }

    public function testDedupeSkipsASecondDispatchOfTheSameClassAndSource()
    {
        $post = Post::findOne(['id' => 2]);
        TestContentNotification::send([1], $post);
        TestContentNotification::send([1, 3], $post);
        $this->assertSame([1, 3], $this->recipientIds(TestContentNotification::class));

        // another source is no duplicate
        TestContentNotification::send([1], Post::findOne(['id' => 7]));
        $this->assertCount(3, $this->rows(TestContentNotification::class));
    }

    public function testDedupeOff()
    {
        $post = Post::findOne(['id' => 2]);
        TestContentNotification::send([1], $post);
        TestContentNotification::send([1], $post, dedupe: false);
        $this->assertSame([1, 1], $this->recipientIds(TestContentNotification::class));
    }

    public function testNoDedupeWithoutSource()
    {
        TestNotification::send([1]);
        TestNotification::send([1]);
        $this->assertSame([1, 1], $this->recipientIds(TestNotification::class));
    }

    public function testRecipientsAsQueryAndAsUser()
    {
        TestNotification::send(User::find()->where(['user.id' => [1, 3]]));
        $this->assertSame([1, 3], $this->recipientIds(TestNotification::class));

        TestHighPriorityNotification::send(User::findOne(['id' => 2]));
        $this->assertSame([2], $this->recipientIds(TestHighPriorityNotification::class));

        Notification::deleteAll();
        TestNotification::send([User::findOne(['id' => 4]), 3, 3]);
        $this->assertSame([3, 4], $this->recipientIds(TestNotification::class));
    }

    public function testDisabledUsersAreSkipped()
    {
        // user 5 is disabled
        TestNotification::send([1, 4, 5]);
        $this->assertSame([1, 4], $this->recipientIds(TestNotification::class));
    }

    public function testRecipientBlockedByTheOriginatorIsSkipped()
    {
        $originator = User::findOne(['id' => 2]);
        // writes user 3 into the originator's block list
        $this->assertTrue(User::findOne(['id' => 3])->blockForUser($originator));
        $this->assertTrue($originator->isBlockedForUser(User::findOne(['id' => 3])));
        $this->assertFalse(User::findOne(['id' => 3])->isBlockedForUser($originator));

        TestNotification::send([1, 3, 4], originator: $originator);
        $this->assertSame([1, 4], $this->recipientIds(TestNotification::class));
    }

    public function testRecipientBlockingTheOriginatorIsSkipped()
    {
        $originator = User::findOne(['id' => 2]);
        // writes the originator into user 3's block list
        $this->assertTrue($originator->blockForUser(User::findOne(['id' => 3])));
        $this->assertTrue(User::findOne(['id' => 3])->isBlockedForUser($originator));
        $this->assertFalse($originator->isBlockedForUser(User::findOne(['id' => 3])));

        TestNotification::send([1, 3, 4], originator: $originator);
        $this->assertSame([1, 4], $this->recipientIds(TestNotification::class));
    }

    public function testContentVisibilityIsChecked()
    {
        // post 11: private post in space 2, of which only user 2 is a member
        TestContentNotification::send([2, 3, 4], Post::findOne(['id' => 11]));
        $this->assertSame([2], $this->recipientIds(TestContentNotification::class));
    }

    public function testCanReceiveFilters()
    {
        TestRejectingNotification::send([1, 3]);
        $this->assertSame([1], $this->recipientIds(TestRejectingNotification::class));
    }

    public function testAFailingRecipientDoesNotAbortTheOthers()
    {
        static::logInitialize();

        TestThrowingNotification::send([1, 3, 2]);

        // user 3's row is written; only the rendering of its live event fails
        $this->assertSame([1, 2, 3], $this->recipientIds(TestThrowingNotification::class));
        static::assertLogRegexCount(1, '/^Live event of notification .*TestThrowingNotification #\\d+ for user 3: .*Rendering failed for user 3/s', Logger::LEVEL_ERROR, ['notification']);
    }

    public function testAFailingFilterDoesNotAbortTheOthers()
    {
        static::logInitialize();

        TestRejectingNotification::send([1, 2], payload: ['throwFor' => 1]);

        $this->assertSame([2], $this->recipientIds(TestRejectingNotification::class));
        static::assertLogRegexCount(1, '/^Notification .*TestRejectingNotification #\\d+ for user 1: .*canReceive failed/s', Logger::LEVEL_ERROR, ['notification']);
    }

    public function testBeforeDispatchEventCanVeto()
    {
        $handler = function (BeforeDispatchEvent $event): void {
            $this->assertSame(TestNotification::class, $event->class);
            $this->assertSame([1, 3], $event->recipients);
            $this->assertNull($event->source);
            $this->assertSame(2, $event->originator->id);
            $this->assertSame(['a' => 1], $event->payload);
            $this->assertFalse($event->notifyOriginator);
            $this->assertTrue($event->dedupe);
            $event->isValid = false;
        };
        Event::on(NotificationManager::class, NotificationManager::EVENT_BEFORE_DISPATCH, $handler);
        try {
            TestNotification::send([1, 3], originator: User::findOne(['id' => 2]), payload: ['a' => 1]);
        } finally {
            Event::off(NotificationManager::class, NotificationManager::EVENT_BEFORE_DISPATCH, $handler);
        }

        $this->assertSame([], $this->rows(TestNotification::class));
    }

    public function testBeforeDispatchEventCanRewriteRecipients()
    {
        $handler = function (BeforeDispatchEvent $event): void {
            $event->recipients = [4];
        };
        Event::on(NotificationManager::class, NotificationManager::EVENT_BEFORE_DISPATCH, $handler);
        try {
            TestNotification::send([1, 3]);
        } finally {
            Event::off(NotificationManager::class, NotificationManager::EVENT_BEFORE_DISPATCH, $handler);
        }

        $this->assertSame([4], $this->recipientIds(TestNotification::class));
    }

    public function testDispatchInsideATransactionIsQueuedAfterTheCommit()
    {
        $jobs = $this->capturePushedJobs(function (): void {
            $transaction = Yii::$app->db->beginTransaction();
            TestNotification::send([1]);
            $this->assertSame([], $this->pushedJobs);
            $transaction->commit();
        });

        $this->assertCount(1, $jobs);
        $this->assertInstanceOf(DispatchJob::class, $jobs[0]);
        $this->assertSame([1], $jobs[0]->recipients);
    }

    public function testDispatchInsideARolledBackTransactionIsDropped()
    {
        $jobs = $this->capturePushedJobs(function (): void {
            $transaction = Yii::$app->db->beginTransaction();
            TestNotification::send([1]);
            $transaction->rollBack();
        });

        $this->assertSame([], $jobs);
    }

    public function testPayloadAndSourceRecord()
    {
        $comment = $this->createComment();
        TestNotification::send([1], $comment, User::findOne(['id' => 2]), payload: ['reason' => 'reply']);

        $row = $this->rows(TestNotification::class)[0];
        $this->assertSame($comment->content->id, (int)$row->content_id);
        $this->assertSame($comment->content->contentcontainer_id, (int)$row->contentcontainer_id);
        $this->assertNotNull($row->source_record_id);

        $notification = NotificationManager::load($row);
        $this->assertInstanceOf(Comment::class, $notification->sourceRecord);
        $this->assertSame($comment->id, $notification->sourceRecord->id);
        $this->assertSame(['reason' => 'reply'], $notification->payload);
    }

    public function testRecordSource()
    {
        // a record that is neither a content nor a container: by its record map id only
        $source = Group::findOne(['id' => 1]);
        TestNotification::send([1], $source);

        $row = $this->rows(TestNotification::class)[0];
        $this->assertNull($row->content_id);
        $this->assertNull($row->contentcontainer_id);
        $this->assertNotNull($row->source_record_id);
    }

    public function testContainerSource()
    {
        TestNotification::send([1, 3], Space::findOne(['id' => 1]));
        $this->assertSame([1, 3], $this->recipientIds(TestNotification::class));
        $row = $this->rows(TestNotification::class)[0];
        $this->assertSame(Space::findOne(['id' => 1])->contentcontainer_id, (int)$row->contentcontainer_id);
        $this->assertNull($row->content_id);
        $this->assertNull($row->source_record_id);

        // space 5 is private (invisible), of which user 3 is no member: a container-sourced
        // notification (e.g. an invite) reaches them all the same
        TestHighPriorityNotification::send([1, 3], Space::findOne(['id' => 5]));
        $this->assertSame([1, 3], $this->recipientIds(TestHighPriorityNotification::class));
    }

    public function testGoneSourceDispatchesNothing()
    {
        foreach ([['content' => 999999], ['container' => 999999], ['record' => 999999]] as $source) {
            (new DispatchJob(['class' => TestNotification::class, 'recipients' => [1], 'source' => $source]))->run();
        }

        $this->assertSame([], $this->rows(TestNotification::class));
    }

    public function testGroupedDispatchRendersTheGroupedSentence()
    {
        $post = Post::findOne(['id' => 2]);
        $live = Yii::$app->get('live');
        $stub = new class {
            public array $events = [];

            public function send($event): void
            {
                $this->events[] = $event;
            }
        };
        Yii::$app->set('live', $stub);
        try {
            TestGroupedNotification::send([1], $post, User::findOne(['id' => 2]));
            TestGroupedNotification::send([1], $post, User::findOne(['id' => 3]));
        } finally {
            Yii::$app->set('live', $live);
        }

        $this->assertCount(2, $stub->events);
        $this->assertStringContainsString('did a thing', $stub->events[0]->text);
        $this->assertStringContainsString('did 2 things', $stub->events[1]->text);

        $rows = $this->rows(TestGroupedNotification::class);
        $this->assertCount(2, $rows);
        $this->assertSame((int)$rows[1]->id, (int)$rows[0]->grouping_key);
        $this->assertSame((int)$rows[1]->id, (int)$rows[1]->grouping_key);

        $this->assertSame(1, (int)Notification::find()->forUser(1)->grouped()->count());
        $head = NotificationManager::load(Notification::find()->forUser(1)->grouped()->one());
        $this->assertSame(2, $head->groupCount);
        $this->assertSame((int)$rows[1]->id, (int)$head->record->id);
    }

    public function testLiveAndUnreadEventForListedNotificationsOnly()
    {
        $count = 0;
        $handler = function () use (&$count): void {
            $count++;
        };
        Event::on(UnreadCountChangedEvent::class, UnreadCountChangedEvent::EVENT_UNREAD_COUNT_CHANGED, $handler);
        try {
            TestNotification::send([1, 3]);
            $this->assertSame(2, $count);
            TestHighPriorityNotification::send([1, 3]);
            $this->assertSame(2, $count);
        } finally {
            Event::off(UnreadCountChangedEvent::class, UnreadCountChangedEvent::EVENT_UNREAD_COUNT_CHANGED, $handler);
        }
    }

    public function testListedFalseDoesNotCount()
    {
        TestHighPriorityNotification::send([1]);
        $this->assertSame(0, (int)$this->rows(TestHighPriorityNotification::class)[0]->listed);
        $this->assertSame(0, (int)Notification::find()->forUser(1)->listed()->count());
    }

    public function testWebSwitchedOffStoresTheRecordUnlisted()
    {
        (new NotificationSettingsService(User::findOne(['id' => 1])))->setCategory(new WebTarget(), NotificationCategory::social(), false);

        TestNotification::send([1, 3]);

        $rows = $this->rows(TestNotification::class);
        $this->assertCount(2, $rows);
        $listed = array_column(array_map(fn(Notification $row) => ['user' => $row->user_id, 'listed' => (int)$row->listed], $rows), 'listed', 'user');
        $this->assertSame([1 => 0, 3 => 1], $listed, 'still stored for the e-mail, but not listed');
        $this->assertSame(0, (int)Notification::find()->forUser(1)->listed()->count());
    }

    public function testNoChannelStoresNothing()
    {
        $settings = new NotificationSettingsService(User::findOne(['id' => 1]));
        $settings->setCategory(new WebTarget(), NotificationCategory::social(), false);
        $settings->setCategory(new MailTarget(), NotificationCategory::social(), false);

        TestNotification::send([1]);
        // not listed at all, and e-mail is off
        TestHighPriorityNotification::send([1]);

        $this->assertCount(0, $this->rows(TestNotification::class));
        $this->assertCount(0, $this->rows(TestHighPriorityNotification::class));
    }

    public function testDeleteByClassSourceAndUser()
    {
        $post = Post::findOne(['id' => 2]);
        TestContentNotification::send([1, 3, 4], $post);
        TestContentNotification::send([1], Post::findOne(['id' => 7]));
        TestNotification::send([1], $post);
        // about a comment under the post: not touched by a deletion by the post
        TestContentNotification::send([1], $this->createComment());

        $this->assertSame(1, TestContentNotification::revoke($post, User::findOne(['id' => 3])));
        $this->assertSame([1, 1, 1, 4], $this->recipientIds(TestContentNotification::class));

        // by the content record or the content itself; the comment's notification stays
        $this->assertSame(2, TestContentNotification::revoke($post->content));
        $this->assertSame([1, 1], $this->recipientIds(TestContentNotification::class));
        $this->assertSame(1, (int)Notification::find()->andWhere(['class' => TestContentNotification::class])->andWhere(['IS NOT', 'source_record_id', null])->count());
        $this->assertCount(1, $this->rows(TestNotification::class));

        // by class only
        $this->assertSame(2, TestContentNotification::revoke());
        $this->assertSame([], $this->rows(TestContentNotification::class));
    }

    public function testDeleteBySourceRecordAndContainer()
    {
        $comment = $this->createComment();
        $this->assertSame(0, TestNotification::revoke(Group::findOne(['id' => 1])), 'no record map row - nothing to match');

        TestNotification::send([1, 3], $comment);
        TestNotification::send([1], Space::findOne(['id' => 1]));
        $this->assertSame(2, TestNotification::revoke($comment));
        $this->assertSame(1, TestNotification::revoke(Space::findOne(['id' => 1])));
    }

    public function testSendWithNamedArguments()
    {
        $post = Post::findOne(['id' => 2]);
        TestContentNotification::send(
            recipients: [1, 2],
            source: $post,
            originator: User::findOne(['id' => 2]),
            payload: ['a' => 1],
            notifyOriginator: true,
        );
        TestContentNotification::send([1], source: $post, originator: User::findOne(['id' => 2]), dedupe: false);

        $rows = $this->rows(TestContentNotification::class);
        $this->assertSame([1, 2, 1], array_map(fn(Notification $row) => (int)$row->user_id, $rows));
        $this->assertSame(NotificationPriority::Normal->value, (int)$rows[0]->priority);
        $this->assertSame(['a' => 1], NotificationManager::fromRecord($rows[0])->payload);

        $this->assertSame(1, TestContentNotification::revoke(source: $post, user: User::findOne(['id' => 2])));
        TestContentNotification::markSeen(source: $post, user: User::findOne(['id' => 1]));
        $this->assertSame(0, (int)Notification::find()->forUser(1)->unseen()->count());
    }

    public function testDeleteByOriginator()
    {
        TestNotification::send([1, 3], originator: User::findOne(['id' => 2]));
        TestNotification::send([1], originator: User::findOne(['id' => 4]));

        $this->assertSame(2, TestNotification::revoke(originator: User::findOne(['id' => 2])));
        $this->assertSame([4], array_map(fn(Notification $n) => (int)$n->originator_id, $this->rows(TestNotification::class)));
    }

    public function testMarkSeenMarksTheWholeGroupAndTriggersTheEvent()
    {
        $post = Post::findOne(['id' => 2]);
        TestGroupedNotification::send([1], $post, User::findOne(['id' => 2]));
        TestGroupedNotification::send([1], $post, User::findOne(['id' => 3]));
        // another recipient of the same class and source
        TestGroupedNotification::send([3], $post, User::findOne(['id' => 2]));

        $count = $this->countUnreadEvents(fn() => TestGroupedNotification::markSeen($post, User::findOne(['id' => 1])));
        $this->assertSame(1, $count);

        $rows = $this->rows(TestGroupedNotification::class);
        $this->assertNotNull($rows[0]->seen_at);
        $this->assertNotNull($rows[1]->seen_at);
        $this->assertNull($rows[2]->seen_at, 'other recipients stay unseen');
    }

    public function testMarkSeenMarksTheGroupOfAMatchingMember()
    {
        $post = Post::findOne(['id' => 2]);
        TestGroupedNotification::send([1], $post, User::findOne(['id' => 2]));
        TestGroupedNotification::send([1], $post, User::findOne(['id' => 3]));
        $rows = $this->rows(TestGroupedNotification::class);
        // the older member is seen already: the group is still marked through it
        Notification::updateAll(['seen_at' => date('Y-m-d H:i:s')], ['id' => $rows[0]->id]);

        TestGroupedNotification::markSeen($post, User::findOne(['id' => 1]));
        $this->assertSame(0, (int)Notification::find()->forUser(1)->unseen()->count());
    }

    public function testMarkRecordSeen()
    {
        $post = Post::findOne(['id' => 2]);
        TestGroupedNotification::send([1], $post, User::findOne(['id' => 2]));
        TestGroupedNotification::send([1], $post, User::findOne(['id' => 3]));
        TestNotification::send([1]);

        $rows = $this->rows(TestGroupedNotification::class);
        $count = $this->countUnreadEvents(fn() => NotificationManager::markRecordSeen($rows[0]));
        $this->assertSame(1, $count);

        $this->assertSame(0, (int)Notification::find()->andWhere(['class' => TestGroupedNotification::class])->unseen()->count());
        $this->assertSame(1, (int)Notification::find()->forUser(1)->unseen()->count());

        $this->assertSame(0, $this->countUnreadEvents(fn() => NotificationManager::markRecordSeen($rows[1])));
    }

    public function testMarkAllSeen()
    {
        TestNotification::send([1, 3]);
        TestHighPriorityNotification::send([1]);

        $this->assertSame(1, $this->countUnreadEvents(fn() => NotificationManager::markAllSeen(User::findOne(['id' => 1]))));
        $this->assertSame(0, (int)Notification::find()->forUser(1)->unseen()->count());
        $this->assertSame(1, (int)Notification::find()->forUser(3)->unseen()->count());
    }

    public function testMarkSeenWithoutUnseenRowsFiresNoEvent()
    {
        $user = User::findOne(['id' => 1]);
        $this->assertSame(0, $this->countUnreadEvents(fn() => NotificationManager::markAllSeen($user)));
        $this->assertSame(0, $this->countUnreadEvents(fn() => TestNotification::markSeen(null, $user)));
        $this->assertSame(0, $this->countUnreadEvents(fn() => TestNotification::markSeen(Group::findOne(['id' => 1]), $user)));
    }

    public function testGetCategoriesListsCoreCategoriesThenModuleCategories()
    {
        $manager = $this->managerWithClasses([
            TestModuleCategoryNotification::class,
            TestNotification::class,
            TestContentNotification::class,
            // an unloadable class is skipped
            'humhub\\modules\\notification\\NoSuchNotification',
        ]);
        $ids = fn(array $categories) => array_map(fn(NotificationCategory $category) => $category->id, $categories);

        $this->assertSame([
            NotificationCategory::ID_DIRECT,
            NotificationCategory::ID_SOCIAL,
            NotificationCategory::ID_FOLLOWERS,
            NotificationCategory::ID_CONTENT,
            'example-reports',
            NotificationCategory::ID_ADMIN,
        ], $ids($manager->getCategories()));

        $this->assertContains(NotificationCategory::ID_ADMIN, $ids($manager->getCategories(User::findOne(['id' => 1]))));
        $this->assertNotContains(NotificationCategory::ID_ADMIN, $ids($manager->getCategories(User::findOne(['id' => 2]))));
    }

    public function testGetNotificationsScansTheCoreModules()
    {
        $manager = new NotificationManager(['targets' => []]);
        $classes = $manager->getNotifications();

        foreach ([
            ContentCreatedNotification::class,
            NewCommentNotification::class,
            NewLikeNotification::class,
            FollowedNotification::class,
            MentionedNotification::class,
            FriendshipRequestNotification::class,
            SpaceInviteNotification::class,
            NewVersionAvailableNotification::class,
            IncludeGroupNotification::class,
            ExcludeGroupNotification::class,
        ] as $class) {
            $this->assertContains($class, $classes);
        }

        // only notification classes, no trait or other file of a notifications/ directory
        $this->assertNotContains(SpaceNotificationTrait::class, $classes);
        foreach ($classes as $class) {
            $this->assertTrue(is_subclass_of($class, BaseNotification::class), $class);
        }

        // no core notification brings a group of its own
        $this->assertSame([
            NotificationCategory::ID_DIRECT,
            NotificationCategory::ID_SOCIAL,
            NotificationCategory::ID_FOLLOWERS,
            NotificationCategory::ID_CONTENT,
            NotificationCategory::ID_ADMIN,
        ], array_map(fn(NotificationCategory $g) => $g->id, $manager->getCategories()));
    }

    public function testSearchEventCanAddNotificationClasses()
    {
        $manager = $this->managerWithClasses([]);
        $manager->on(NotificationManager::EVENT_SEARCH_MODULE_NOTIFICATIONS, function ($event): void {
            $event->result[] = TestModuleCategoryNotification::class;
        });

        $this->assertSame([TestModuleCategoryNotification::class], $manager->getNotifications());
        $this->assertContains('example-reports', array_map(fn(NotificationCategory $g) => $g->id, $manager->getCategories()));
    }

    public function testTargetsAreDedupedById()
    {
        $manager = new NotificationManager(['targets' => [
            'web' => ['class' => WebTarget::class, 'delays' => [0]],
            'email' => ['class' => MailTarget::class, 'delayWindow' => 7200],
            // a legacy entry keyed by class name overrides the core entry of the same id
            WebTarget::class => ['skipWhenOnline' => true],
            // a numeric key keeps the target's own id
            ['class' => MailTarget::class, 'lowPriorityDelay' => 60],
        ]]);

        $targets = $manager->getTargets();
        $this->assertSame(['web', 'email'], array_map(fn($t) => $t->id, $targets));
        $this->assertTrue($manager->getTarget('web')->skipWhenOnline);
        $this->assertSame([0], $manager->getTarget('web')->delays);
        $this->assertSame(7200, $manager->getTarget('email')->delayWindow);
        $this->assertSame(60, $manager->getTarget('email')->lowPriorityDelay);
    }

    public function testLegacyTargetKeysAreDroppedWithAWarning()
    {
        static::logInitialize();
        $manager = new NotificationManager(['targets' => [
            'email' => ['class' => MailTarget::class],
            // removed properties of a legacy configuration keyed by class name
            MailTarget::class => ['renderer' => ['class' => 'app\\MailRenderer'], 'defaultSetting' => true, 'delayWindow' => 600],
        ]]);

        $this->assertSame(['email'], array_map(fn($t) => $t->id, $manager->getTargets()));
        $this->assertSame(600, $manager->getTarget('email')->delayWindow);
        static::assertLogRegexCount(1, '/"renderer" of the notification target "email" is ignored/', Logger::LEVEL_WARNING, ['notification']);
        static::assertLogRegexCount(1, '/"defaultSetting" of the notification target "email" is ignored/', Logger::LEVEL_WARNING, ['notification']);
    }

    public function testGetTargetById()
    {
        $this->assertInstanceOf(MailTarget::class, Yii::$app->notification->getTarget('email'));
        $this->assertSame('email', Yii::$app->notification->getTarget('email')->id);
        static::logInitialize();
        $this->assertSame(Yii::$app->notification->getTarget('email'), Yii::$app->notification->getTarget(MailTarget::class));
        static::assertLogRegexCount(1, '/getTarget\(\) by class name is deprecated/', Logger::LEVEL_WARNING, ['notification']);
        $this->assertNull(Yii::$app->notification->getTarget('unknown'));
        // the mobile target is inactive without a push provider
        $this->assertSame(['web', 'email'], array_map(fn($t) => $t->id, Yii::$app->notification->getTargets()));
    }

    /**
     * @return Notification[] the rows of the class, oldest first
     */
    private function rows(string $class): array
    {
        return Notification::find()->andWhere(['class' => $class])->orderBy(['id' => SORT_ASC])->all();
    }

    /**
     * @return int[] the recipients of the rows of the class, sorted
     */
    private function recipientIds(string $class): array
    {
        $ids = array_map(fn(Notification $n) => (int)$n->user_id, $this->rows($class));
        sort($ids);

        return $ids;
    }

    /**
     * A manager whose module scan finds the given classes only.
     */
    private function managerWithClasses(array $classes): NotificationManager
    {
        return new class (['targets' => []], $classes) extends NotificationManager {
            public function __construct(array $config, private readonly array $classes)
            {
                parent::__construct($config);
            }

            protected function findNotificationClasses(): array
            {
                return $this->classes;
            }
        };
    }

    private array $pushedJobs = [];

    /**
     * Runs the callback outside the transaction the test harness wraps around the test (no data
     * is written: the pushed jobs are recorded, not queued) and returns the pushed jobs.
     */
    private function capturePushedJobs(callable $callback): array
    {
        $db = Yii::$app->db;
        while ($db->getTransaction()?->getIsActive()) {
            $db->getTransaction()->rollBack();
        }
        AfterCommit::$immediate = false;

        $this->pushedJobs = [];
        $handler = function (PushEvent $event): void {
            $this->pushedJobs[] = $event->job;
            $event->handled = true;
        };
        Yii::$app->queue->on(Queue::EVENT_BEFORE_PUSH, $handler);
        try {
            $callback();
        } finally {
            Yii::$app->queue->off(Queue::EVENT_BEFORE_PUSH, $handler);
        }

        return $this->pushedJobs;
    }

    private function countUnreadEvents(callable $callback): int
    {
        $count = 0;
        $handler = function () use (&$count): void {
            $count++;
        };
        Event::on(UnreadCountChangedEvent::class, UnreadCountChangedEvent::EVENT_UNREAD_COUNT_CHANGED, $handler);
        try {
            $callback();
        } finally {
            Event::off(UnreadCountChangedEvent::class, UnreadCountChangedEvent::EVENT_UNREAD_COUNT_CHANGED, $handler);
        }

        return $count;
    }

    /**
     * A comment of User1 on post 2.
     */
    private function createComment(): Comment
    {
        $this->becomeUser('User1');
        $comment = new Comment(['message' => 'Test comment', 'content_id' => Post::findOne(['id' => 2])->content->id]);
        $this->assertTrue($comment->save());

        return $comment;
    }
}
