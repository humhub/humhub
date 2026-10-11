<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use humhub\components\db\AfterCommit;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\components\NotificationSet;
use humhub\modules\notification\events\BeforeDispatchEvent;
use humhub\modules\notification\jobs\DispatchJob;
use humhub\modules\notification\models\Notification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestContentNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestDirectNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestRejectingNotification;
use humhub\modules\post\models\Post;
use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\Event;
use yii\base\InvalidArgumentException;
use yii\queue\PushEvent;
use yii\queue\Queue;

/**
 * The queue of the test application is synchronous, so {@see NotificationSet::send()} runs the
 * {@see DispatchJob} inline.
 */
class NotificationSetTest extends HumHubDbTestCase
{
    public $fixtureConfig = ['default'];

    public function testEachRecipientIsNotifiedByTheFirstMatchingEntry()
    {
        NotificationSet::create(originator: User::findOne(['id' => 2]))
            ->add(TestDirectNotification::class, [1, 3])
            ->add(TestNotification::class, [1, 3, 4])
            ->send();

        $this->assertSame([1, 3], $this->recipientIds(TestDirectNotification::class));
        $this->assertSame([4], $this->recipientIds(TestNotification::class));
    }

    public function testTheOrderOfTheEntriesIsTheRank()
    {
        NotificationSet::create()
            ->add(TestNotification::class, [1, 3, 4])
            ->add(TestDirectNotification::class, [1, 3])
            ->send();

        $this->assertSame([1, 3, 4], $this->recipientIds(TestNotification::class));
        $this->assertSame([], $this->recipientIds(TestDirectNotification::class));
    }

    public function testARecipientRejectedByCanReceiveFallsThroughToALaterEntry()
    {
        // TestRejectingNotification rejects user 3
        NotificationSet::create()
            ->add(TestRejectingNotification::class, [1, 3])
            ->add(TestNotification::class, [1, 3])
            ->send();

        $this->assertSame([1], $this->recipientIds(TestRejectingNotification::class));
        $this->assertSame([3], $this->recipientIds(TestNotification::class));
    }

    public function testARecipientWhoAlreadyHasTheNotificationCountsAsNotified()
    {
        $post = Post::findOne(['id' => 2]);
        TestContentNotification::send([1], $post);

        NotificationSet::create(source: $post)
            ->add(TestContentNotification::class, [1, 3])
            ->add(TestNotification::class, [1, 3, 4])
            ->send();

        $this->assertSame([1, 3], $this->recipientIds(TestContentNotification::class));
        $this->assertSame([4], $this->recipientIds(TestNotification::class));
    }

    public function testTheOriginatorIsSkippedInEveryEntry()
    {
        NotificationSet::create(originator: User::findOne(['id' => 2]))
            ->add(TestDirectNotification::class, [2])
            ->add(TestNotification::class, [1, 2])
            ->send();

        $this->assertSame([], $this->recipientIds(TestDirectNotification::class));
        $this->assertSame([1], $this->recipientIds(TestNotification::class));
    }

    public function testEntryOptionsAndSource()
    {
        $post = Post::findOne(['id' => 2]);
        $other = Post::findOne(['id' => 7]);

        NotificationSet::create(source: $post, originator: User::findOne(['id' => 2]))
            ->add(TestDirectNotification::class, [2], source: $other, payload: ['a' => 1], notifyOriginator: true)
            ->add(TestContentNotification::class, [1, 2])
            ->send();

        $direct = $this->rows(TestDirectNotification::class);
        $this->assertCount(1, $direct);
        $this->assertSame($other->content->id, (int)$direct[0]->content_id);
        $this->assertSame(['a' => 1], NotificationManager::load($direct[0])->payload);

        $content = $this->rows(TestContentNotification::class);
        $this->assertSame([1], $this->recipientIds(TestContentNotification::class));
        $this->assertSame($post->content->id, (int)$content[0]->content_id);
    }

    public function testBeforeDispatchVetoDropsThatEntryOnly()
    {
        $classes = [];
        $handler = function (BeforeDispatchEvent $event) use (&$classes): void {
            $classes[] = $event->class;
            if ($event->class === TestDirectNotification::class) {
                $event->isValid = false;
            }
        };
        Event::on(NotificationManager::class, NotificationManager::EVENT_BEFORE_DISPATCH, $handler);
        try {
            NotificationSet::create()
                ->add(TestDirectNotification::class, [1, 3])
                ->add(TestNotification::class, [1, 3])
                ->send();
        } finally {
            Event::off(NotificationManager::class, NotificationManager::EVENT_BEFORE_DISPATCH, $handler);
        }

        $this->assertSame([TestDirectNotification::class, TestNotification::class], $classes);
        $this->assertSame([], $this->recipientIds(TestDirectNotification::class));
        $this->assertSame([1, 3], $this->recipientIds(TestNotification::class));
    }

    public function testOneJobIsQueuedAfterTheCommitAndQueriesAreResolvedInTheJob()
    {
        $jobs = $this->capturePushedJobs(function (): void {
            $transaction = Yii::$app->db->beginTransaction();
            NotificationSet::create()
                ->add(TestDirectNotification::class, User::findOne(['id' => 1]))
                ->add(TestNotification::class, User::find()->where(['user.id' => [1, 3]]))
                ->send();
            $this->assertSame([], $this->pushedJobs);
            $transaction->commit();
        });

        $this->assertCount(1, $jobs);
        $this->assertInstanceOf(DispatchJob::class, $jobs[0]);
        $this->assertCount(2, $jobs[0]->entries);
        $this->assertSame(TestDirectNotification::class, $jobs[0]->entries[0]['class']);
        $this->assertSame([1], $jobs[0]->entries[0]['recipients']);
        $this->assertInstanceOf(ActiveQueryUser::class, $jobs[0]->entries[1]['recipients']);
    }

    public function testARolledBackSetIsDropped()
    {
        $jobs = $this->capturePushedJobs(function (): void {
            $transaction = Yii::$app->db->beginTransaction();
            NotificationSet::create()->add(TestNotification::class, [1])->send();
            $transaction->rollBack();
        });

        $this->assertSame([], $jobs);
    }

    public function testNothingIsQueuedWithoutEntries()
    {
        $jobs = $this->capturePushedJobs(function (): void {
            NotificationSet::create()->send();

            $veto = fn(BeforeDispatchEvent $event) => $event->isValid = false;
            Event::on(NotificationManager::class, NotificationManager::EVENT_BEFORE_DISPATCH, $veto);
            try {
                NotificationSet::create()->add(TestNotification::class, [1])->send();
            } finally {
                Event::off(NotificationManager::class, NotificationManager::EVENT_BEFORE_DISPATCH, $veto);
            }
        });

        $this->assertSame([], $jobs);
    }

    public function testRejectsAClassThatIsNoNotification()
    {
        $this->expectException(InvalidArgumentException::class);
        NotificationSet::create()->add(Post::class, [1]);
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
}
