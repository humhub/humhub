<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use Closure;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\components\NotificationPriority;
use humhub\modules\notification\jobs\DeliverJob;
use humhub\modules\notification\jobs\DispatchJob;
use humhub\modules\notification\models\NotificationDelivery;
use humhub\modules\notification\services\DeliveryScheduler;
use humhub\modules\notification\targets\DeliveryBatch;
use humhub\modules\notification\targets\MailTarget;
use humhub\modules\notification\targets\MobileTarget;
use humhub\modules\notification\targets\MobileTargetProvider;
use humhub\modules\notification\targets\WebTarget;
use humhub\modules\notification\tests\codeception\unit\notifications\TestNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestPriorityTrait;
use humhub\modules\user\models\User;
use RuntimeException;
use Yii;
use yii\queue\PushEvent;
use yii\queue\Queue;
use yii\symfonymailer\Message;

/**
 * Shared setup of the delivery layer tests.
 *
 * The test queue runs every job at once and ignores its delay, so the scheduler would treat every
 * delay as 0 ({@see DeliveryScheduler::isInstant()}). These tests switch that off
 * (`instantDelivery = false`), configure the
 * targets with the real default timings, freeze the clock of the delivery layer
 * ({@see DeliveryScheduler::$clock}) and run the {@see DeliverJob} explicitly after moving the
 * clock on.
 */
trait DeliveryTestTrait
{
    /**
     * @var int the frozen "now" of the delivery layer
     */
    protected int $now;

    /**
     * @var PushEvent[] the jobs pushed since {@see setUpDelivery()}
     */
    protected array $pushes = [];

    /**
     * @param array $email properties of the mail target over the defaults
     * @param MobileTargetProvider|null $provider a push provider; the mobile target is inactive without
     */
    protected function setUpDelivery(array $email = [], ?MobileTargetProvider $provider = null): void
    {
        $this->now = time();
        DeliveryScheduler::$clock = fn() => $this->now;
        // rows other suites left behind would count as messages sent within the window
        NotificationDelivery::deleteAll();

        Yii::$app->set('notification', [
            'class' => NotificationManager::class,
            'instantDelivery' => false,
            'targets' => [
                'web' => ['class' => WebTarget::class],
                'email' => array_merge(['class' => MailTarget::class], $this->mailTargetConfig(), $email),
                'mobile' => ['class' => MobileTarget::class, 'provider' => $provider],
            ],
        ]);

        $this->pushes = [];
        Yii::$app->queue->off(Queue::EVENT_AFTER_PUSH, [$this, 'recordPush']);
        Yii::$app->queue->on(Queue::EVENT_AFTER_PUSH, [$this, 'recordPush']);
    }

    /**
     * @return array properties of the mail target over its defaults for every {@see setUpDelivery()}
     * of the test class
     */
    protected function mailTargetConfig(): array
    {
        return [];
    }

    public function recordPush(PushEvent $event): void
    {
        $this->pushes[] = $event;
    }

    protected function tearDown(): void
    {
        DeliveryScheduler::$clock = null;
        parent::tearDown();
    }

    /**
     * Dispatches a notification without source (so no dedupe) from user 2 to user 1, with the
     * priority set on the class for this dispatch. The dispatch job runs within it, also on a
     * {@see DelayingTestQueue}.
     *
     * @param string $class a test notification class using {@see TestPriorityTrait}
     */
    protected function dispatchToAdmin(NotificationPriority $priority = NotificationPriority::Normal, string $class = TestNotification::class, $source = null, int $originatorId = 2): void
    {
        $class::$testPriority = $priority;
        try {
            $class::send([1], $source, User::findOne(['id' => $originatorId]));
            if (Yii::$app->queue instanceof DelayingTestQueue) {
                $this->runPushed(DispatchJob::class);
            }
        } finally {
            $class::$testPriority = null;
        }
    }

    /**
     * Runs the recorded jobs of the class (for a queue that does not run them itself) and forgets them.
     */
    protected function runPushed(string $class): void
    {
        foreach ($this->pushes as $index => $event) {
            if ($event->job instanceof $class) {
                unset($this->pushes[$index]);
                $event->job->run();
            }
        }
    }

    protected function runDeliverJob(string $channel = 'email', int $userId = 1): void
    {
        (new DeliverJob(['userId' => $userId, 'channel' => $channel]))->run();
    }

    /**
     * @return NotificationDelivery[] oldest first
     */
    protected function deliveries(string $channel = 'email', int $userId = 1): array
    {
        return NotificationDelivery::find()->forUserAndChannel($userId, $channel)->orderBy(['id' => SORT_ASC])->all();
    }

    protected function lastDelivery(string $channel = 'email'): NotificationDelivery
    {
        $deliveries = $this->deliveries($channel);
        return end($deliveries);
    }

    protected function assertDueIn(int $seconds, NotificationDelivery $delivery): void
    {
        $this->assertSame(date('Y-m-d H:i:s', $this->now + $seconds), $delivery->due_at);
    }

    /**
     * @return PushEvent[] the pushed delivery jobs
     */
    protected function deliverJobPushes(): array
    {
        return array_values(array_filter($this->pushes, fn(PushEvent $event) => $event->job instanceof DeliverJob));
    }

    /**
     * @return Message[]
     */
    protected function mails(): array
    {
        return $this->getYiiModule()->grabSentEmails();
    }

    /**
     * @param Closure|null $onDeliver called with each batch before it is recorded
     */
    protected function provider(bool $failing = false, ?Closure $onDeliver = null): MobileTargetProvider
    {
        return new class ($failing, $onDeliver) implements MobileTargetProvider {
            /** @var DeliveryBatch[] */
            public array $batches = [];

            public function __construct(private readonly bool $failing, private readonly ?Closure $onDeliver)
            {
            }

            public function deliver(DeliveryBatch $batch): void
            {
                if ($this->failing) {
                    throw new RuntimeException('Push gateway down');
                }
                if ($this->onDeliver !== null) {
                    ($this->onDeliver)($batch);
                }
                $this->batches[] = $batch;
            }

            public function isActive(?User $user = null): bool
            {
                return true;
            }
        };
    }
}
