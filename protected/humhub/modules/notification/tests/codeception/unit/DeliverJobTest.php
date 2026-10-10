<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\components\NotificationPriority;
use humhub\modules\notification\jobs\DeliverJob;
use humhub\modules\notification\models\Notification;
use humhub\modules\notification\models\NotificationDelivery;
use humhub\modules\notification\services\NotificationSettingsService;
use humhub\modules\notification\targets\MailTarget;
use humhub\modules\notification\targets\WebTarget;
use humhub\modules\notification\tests\codeception\unit\notifications\TestFlakyNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestGroupedNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestStandaloneNotification;
use humhub\modules\post\models\Post;
use humhub\modules\user\models\User;
use humhub\modules\user\services\IsOnlineService;
use humhub\tests\codeception\unit\components\MutexMock;
use tests\codeception\_support\HumHubDbTestCase;
use humhub\modules\notification\tests\codeception\unit\notifications\TestNotification;
use Yii;
use yii\log\Logger;

/**
 * {@see DeliverJob} with the default timings of the mail target. Every scenario starts with a
 * first notification that goes out at once, so the following ones wait 300 s and ride along.
 */
class DeliverJobTest extends HumHubDbTestCase
{
    use DeliveryTestTrait;

    public $fixtureConfig = ['default'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDelivery();
    }

    public function testCollectsTheDueNotificationsInOneMail()
    {
        $this->dispatchToAdmin();
        for ($i = 0; $i < 3; $i++) {
            $this->now += 10;
            $this->dispatchToAdmin();
        }
        $this->assertCount(1, $this->mails());

        $this->now += 300;
        $this->runDeliverJob();

        $mails = $this->mails();
        $this->assertCount(2, $mails);
        $this->assertSame('3 new notifications', $mails[1]->getSubject());
        $sentAt = array_unique(array_map(fn(NotificationDelivery $delivery) => $delivery->sent_at, array_slice($this->deliveries(), 1)));
        $this->assertSame([date('Y-m-d H:i:s', $this->now)], array_values($sentAt));
    }

    public function testStandaloneNotificationsGoOutAlone()
    {
        $this->dispatchToAdmin();
        $this->now += 10;
        $this->dispatchToAdmin(NotificationPriority::Normal, TestStandaloneNotification::class);
        $this->now += 10;
        $this->dispatchToAdmin();
        $this->now += 10;
        $this->dispatchToAdmin(NotificationPriority::Normal, TestStandaloneNotification::class, null, 3);
        $this->now += 10;
        $this->dispatchToAdmin();
        $this->assertCount(1, $this->mails());

        $this->now += 300;
        $this->runDeliverJob();

        // each standalone one in a mail of its own, the two others together
        $mails = $this->mails();
        $this->assertCount(4, $mails);
        $this->assertSame('Peter Tester published an article', $mails[1]->getSubject());
        $this->assertSame(User::findOne(['id' => 3])->displayName . ' published an article', $mails[2]->getSubject());
        $this->assertSame('2 new notifications', $mails[3]->getSubject());
        $this->assertStringNotContainsString('published an article', $mails[3]->getSymfonyEmail()->getTextBody());
        foreach ($this->deliveries() as $delivery) {
            $this->assertSame(NotificationDelivery::STATE_SENT, (int)$delivery->state);
        }
    }

    public function testStandaloneNotificationAloneIsOneMail()
    {
        $this->dispatchToAdmin();
        $this->now += 10;
        $this->dispatchToAdmin(NotificationPriority::Normal, TestStandaloneNotification::class);

        $this->now += 300;
        $this->runDeliverJob();

        $mails = $this->mails();
        $this->assertCount(2, $mails);
        $this->assertSame('Peter Tester published an article', $mails[1]->getSubject());
        $this->assertSame(NotificationDelivery::STATE_SENT, (int)$this->lastDelivery()->state);
    }

    public function testFailingStandaloneMessageFailsOnlyItsRows()
    {
        $provider = $this->provider(false, function ($batch): void {
            if ($batch->first() instanceof TestStandaloneNotification) {
                throw new \RuntimeException('Rejected');
            }
        });
        $this->setUpDelivery([], $provider);
        static::logInitialize();

        $this->dispatchToAdmin();
        $this->now += 10;
        $this->dispatchToAdmin(NotificationPriority::Normal, TestStandaloneNotification::class);
        $this->now += 10;
        $this->dispatchToAdmin();

        $this->now += 300;
        $this->runDeliverJob('mobile');

        $this->assertCount(2, $provider->batches);
        $this->assertTrue($provider->batches[1]->isSingle());
        $deliveries = $this->deliveries('mobile');
        $this->assertSame(NotificationDelivery::STATE_FAILED, (int)$deliveries[1]->state);
        $this->assertSame(NotificationDelivery::STATE_SENT, (int)$deliveries[2]->state);
        static::assertLogRegexCount(1, '/^Notification #' . $deliveries[1]->notification_id . ' for user 1: delivery through mobile failed/', Logger::LEVEL_ERROR, ['notification']);
    }

    public function testGroupedNotificationsCollapseToOneEntry()
    {
        $post = Post::findOne(['id' => 2]);
        $this->dispatchToAdmin(NotificationPriority::Normal, TestGroupedNotification::class, $post, 2);
        $this->now += 10;
        $this->dispatchToAdmin(NotificationPriority::Normal, TestGroupedNotification::class, $post, 3);
        $this->now += 10;
        $this->dispatchToAdmin(NotificationPriority::Normal, TestGroupedNotification::class, $post, 4);

        $this->now += 300;
        $this->runDeliverJob();

        $mails = $this->mails();
        $this->assertCount(2, $mails);
        // one entry: the group's sentence, not "2 new notifications"
        $this->assertStringContainsString('did 3 things', $mails[1]->getSubject());
        $this->assertStringContainsString('did 3 things', $mails[1]->getSymfonyEmail()->getTextBody());
        $this->assertSame([NotificationDelivery::STATE_SENT], array_values(array_unique(array_map(fn($d) => (int)$d->state, $this->deliveries()))));
    }

    public function testPendingRowsOfTheDeliveredGroupGoAlong()
    {
        $post = Post::findOne(['id' => 2]);
        // an unrelated first message, so the group members wait
        $this->dispatchToAdmin();
        $this->now += 10;
        $this->dispatchToAdmin(NotificationPriority::Normal, TestGroupedNotification::class, $post, 3);
        $this->now += 10;
        $this->dispatchToAdmin(NotificationPriority::Normal, TestGroupedNotification::class, $post, 4);
        // the newer member due later
        $newer = $this->lastDelivery();
        NotificationDelivery::updateAll(['due_at' => date('Y-m-d H:i:s', $this->now + 2000)], ['id' => $newer->id]);

        $this->now += 290;
        $this->runDeliverJob();

        $mails = $this->mails();
        $this->assertCount(2, $mails);
        $this->assertStringContainsString('did 2 things', $mails[1]->getSubject());
        $deliveries = $this->deliveries();
        $this->assertSame(NotificationDelivery::STATE_SENT, (int)$deliveries[1]->state);
        $this->assertSame(NotificationDelivery::STATE_SENT, (int)$deliveries[2]->state);
        $this->assertSame($deliveries[1]->sent_at, $deliveries[2]->sent_at);

        // the job of the newer member's original due time
        $this->now += 2000;
        $this->runDeliverJob();
        $this->assertCount(2, $this->mails());
    }

    public function testSeenNotificationIsSkipped()
    {
        $this->dispatchToAdmin();
        $this->now += 10;
        $this->dispatchToAdmin();
        NotificationManager::markAllSeen(User::findOne(['id' => 1]));

        $this->now += 300;
        $this->runDeliverJob();

        $this->assertCount(1, $this->mails());
        $this->assertSame(NotificationDelivery::STATE_SKIPPED, (int)$this->lastDelivery()->state);
        $this->assertNull($this->lastDelivery()->sent_at);
    }

    public function testOnlineRecipientIsSkippedForMailButNotForPush()
    {
        $provider = $this->provider();
        $this->setUpDelivery([], $provider);
        (new IsOnlineService(User::findOne(['id' => 1])))->updateStatus();

        $this->dispatchToAdmin();

        $this->assertCount(0, $this->mails());
        $this->assertSame(NotificationDelivery::STATE_SKIPPED, (int)$this->lastDelivery('email')->state);
        $this->assertCount(1, $provider->batches);
        $this->assertSame(NotificationDelivery::STATE_SENT, (int)$this->lastDelivery('mobile')->state);
    }

    public function testOnlineRecipientKeepsTheRowsNotDueYet()
    {
        Yii::$app->set('queue', new DelayingTestQueue());
        $this->setUpDelivery();
        (new IsOnlineService(User::findOne(['id' => 1])))->updateStatus();

        $this->dispatchToAdmin(NotificationPriority::Low);
        $this->dispatchToAdmin();
        $this->pushes = [];

        $this->runDeliverJob();

        // the due one is skipped, the later one waits for its follow-up
        $deliveries = $this->deliveries();
        $this->assertSame(NotificationDelivery::STATE_PENDING, (int)$deliveries[0]->state);
        $this->assertSame(NotificationDelivery::STATE_SKIPPED, (int)$deliveries[1]->state);
        $this->assertCount(0, $this->mails());
        $followUps = $this->deliverJobPushes();
        $this->assertCount(1, $followUps);
        $this->assertSame(1800, $followUps[0]->delay);

        // the user has left by then
        Yii::$app->cache->flush();
        $this->now += 1800;
        $followUps[0]->job->run();

        $this->assertCount(1, $this->mails());
        $this->assertSame(NotificationDelivery::STATE_SENT, (int)$this->deliveries()[0]->state);
    }

    public function testFollowUpAfterANothingDueRunWithADelayingQueue()
    {
        Yii::$app->set('queue', new DelayingTestQueue());
        $this->setUpDelivery();

        $this->dispatchToAdmin(NotificationPriority::Low);
        $this->pushes = [];

        // e.g. a job that ran early
        $this->now += 100;
        $this->runDeliverJob();

        $followUps = $this->deliverJobPushes();
        $this->assertCount(1, $followUps);
        $this->assertSame(1700, $followUps[0]->delay);
    }

    public function testChannelDisabledMeanwhileIsSkipped()
    {
        $this->dispatchToAdmin();
        $this->now += 10;
        $this->dispatchToAdmin();
        (new NotificationSettingsService(User::findOne(['id' => 1])))->setCategory(new MailTarget(), TestNotification::category(), false);

        $this->now += 300;
        $this->runDeliverJob();

        $this->assertCount(1, $this->mails());
        $this->assertSame(NotificationDelivery::STATE_SKIPPED, (int)$this->lastDelivery()->state);
    }

    public function testNotificationThatNoLongerLoadsIsSkipped()
    {
        $this->dispatchToAdmin();
        $this->now += 10;
        $this->dispatchToAdmin();
        // e.g. the class of an uninstalled module
        Notification::updateAll(['class' => 'humhub\modules\gone\notifications\GoneNotification'], ['id' => $this->lastDelivery()->notification_id]);

        $this->now += 300;
        $this->runDeliverJob();

        $this->assertCount(1, $this->mails());
        $this->assertSame(NotificationDelivery::STATE_SKIPPED, (int)$this->lastDelivery()->state);
    }

    public function testDisabledRecipientIsSkipped()
    {
        $this->dispatchToAdmin();
        $this->now += 10;
        $this->dispatchToAdmin();
        User::updateAll(['status' => User::STATUS_DISABLED], ['id' => 1]);

        $this->now += 300;
        $this->runDeliverJob();

        $this->assertCount(1, $this->mails());
        $this->assertSame(NotificationDelivery::STATE_SKIPPED, (int)$this->lastDelivery()->state);
    }

    public function testRowsOfAGoneTargetAreSkipped()
    {
        $this->dispatchToAdmin();
        $this->now += 10;
        $this->dispatchToAdmin();
        Yii::$app->set('notification', [
            'class' => NotificationManager::class,
            'targets' => ['web' => ['class' => WebTarget::class]],
        ]);

        $this->now += 300;
        $this->runDeliverJob();

        $this->assertCount(1, $this->mails());
        $this->assertSame(NotificationDelivery::STATE_SKIPPED, (int)$this->lastDelivery()->state);
    }

    public function testThrowingEnabledCheckIsSkipped()
    {
        $this->dispatchToAdmin();
        $this->now += 10;
        $this->dispatchToAdmin(NotificationPriority::Normal, TestFlakyNotification::class);
        static::logInitialize();

        TestFlakyNotification::$failCategory = true;
        try {
            $this->now += 300;
            $this->runDeliverJob();
        } finally {
            TestFlakyNotification::$failCategory = false;
        }

        $this->assertCount(1, $this->mails());
        $this->assertSame(NotificationDelivery::STATE_SKIPPED, (int)$this->lastDelivery()->state);
        static::assertLogRegexCount(1, '/Could not check the channel email for .*TestFlakyNotification: .*Category lookup failed/s', Logger::LEVEL_ERROR, ['notification']);
    }

    public function testNotificationThatFailsToConstructIsSkipped()
    {
        $this->dispatchToAdmin();
        $this->now += 10;
        $this->dispatchToAdmin();
        $this->now += 10;
        $this->dispatchToAdmin(NotificationPriority::Normal, TestFlakyNotification::class);
        static::logInitialize();

        TestFlakyNotification::$failInit = true;
        try {
            $this->now += 300;
            $this->runDeliverJob();
        } finally {
            TestFlakyNotification::$failInit = false;
        }

        // the other one still goes out
        $mails = $this->mails();
        $this->assertCount(2, $mails);
        $this->assertSame('Peter Tester did a thing', $mails[1]->getSubject());
        $deliveries = $this->deliveries();
        $this->assertSame(NotificationDelivery::STATE_SENT, (int)$deliveries[1]->state);
        $this->assertSame(NotificationDelivery::STATE_SKIPPED, (int)$deliveries[2]->state);
        static::assertLogRegexCount(1, '/^Notification #' . $deliveries[2]->notification_id . ' \\(group \\d+\\) for user 1 is not delivered through email, it does not load: .*Construction failed/s', Logger::LEVEL_WARNING, ['notification']);
    }

    public function testFailingTargetMarksTheRowsFailed()
    {
        $this->setUpDelivery([], $this->provider(true));
        static::logInitialize();

        $this->dispatchToAdmin();

        $this->assertSame(NotificationDelivery::STATE_FAILED, (int)$this->lastDelivery('mobile')->state);
        static::assertLogRegexCount(1, '/for user 1: delivery through mobile failed: .*Push gateway down/s', Logger::LEVEL_ERROR, ['notification']);
        // the other channel is not affected
        $this->assertSame(NotificationDelivery::STATE_SENT, (int)$this->lastDelivery('email')->state);
        $this->assertCount(1, $this->mails());
    }

    public function testTakesAllPendingRowsAlong()
    {
        $this->dispatchToAdmin();
        $this->now += 10;
        // waits 1800 s
        $this->dispatchToAdmin(NotificationPriority::Low);
        $this->now += 10;
        // waits 300 s: due earlier than the pending one, so it gets a job of its own
        $this->dispatchToAdmin();
        $this->pushes = [];

        $this->now += 300;
        $this->runDeliverJob();

        $mails = $this->mails();
        $this->assertCount(2, $mails);
        $this->assertSame('2 new notifications', $mails[1]->getSubject());
        foreach ($this->deliveries() as $delivery) {
            $this->assertSame(NotificationDelivery::STATE_SENT, (int)$delivery->state);
        }
        // nothing left for a follow-up
        $this->assertCount(0, $this->deliverJobPushes());
    }

    public function testFollowUpForARowWrittenWhileTheJobRuns()
    {
        $dispatched = false;
        $provider = $this->provider(false, function () use (&$dispatched): void {
            if (!$dispatched) {
                $dispatched = true;
                // written while the job holds the lock: its own job finds the lock taken
                $this->dispatchToAdmin(NotificationPriority::High);
            }
        });
        $this->setUpDelivery([], $provider);

        $this->dispatchToAdmin(NotificationPriority::High);

        // the follow-up, pushed after the lock was released, delivered the second one
        $this->assertCount(2, $provider->batches);
        $deliveries = $this->deliveries('mobile');
        $this->assertCount(2, $deliveries);
        foreach ($deliveries as $delivery) {
            $this->assertSame(NotificationDelivery::STATE_SENT, (int)$delivery->state);
        }
    }

    public function testNothingDueSendsNothing()
    {
        $this->runDeliverJob();
        $this->assertCount(0, $this->mails());
        $this->assertCount(0, $this->deliverJobPushes());

        // the job of the second notification, which ran at once in the test queue
        $this->dispatchToAdmin();
        $this->now += 10;
        $this->dispatchToAdmin();
        $this->runDeliverJob();
        $this->assertCount(1, $this->mails());
        $this->assertSame(NotificationDelivery::STATE_PENDING, (int)$this->lastDelivery()->state);
    }

    public function testDoubledJobIsHarmless()
    {
        $this->dispatchToAdmin();
        $this->now += 10;
        $this->dispatchToAdmin();

        $this->now += 300;
        $this->runDeliverJob();
        $this->runDeliverJob();

        $this->assertCount(2, $this->mails());
    }

    public function testLockedChannelIsLeftToTheRunningJob()
    {
        $this->dispatchToAdmin();
        $this->now += 10;
        $this->dispatchToAdmin();

        Yii::$app->set('mutex', new MutexMock(['available' => false]));
        $this->now += 300;
        $this->runDeliverJob();

        $this->assertCount(1, $this->mails());
        $this->assertSame(NotificationDelivery::STATE_PENDING, (int)$this->lastDelivery()->state);
    }
}
