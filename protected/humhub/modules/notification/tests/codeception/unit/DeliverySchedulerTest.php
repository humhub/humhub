<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use humhub\modules\notification\components\NotificationPriority;
use humhub\modules\notification\models\NotificationDelivery;
use humhub\modules\notification\services\DeliveryScheduler;
use humhub\modules\notification\services\NotificationSettingsService;
use humhub\modules\notification\targets\MailTarget;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use humhub\modules\notification\tests\codeception\unit\notifications\TestNotification;
use Yii;

/**
 * {@see DeliveryScheduler} with the default timings of the mail target: delays 0/300/900/1800 s
 * within a window of 3600 s, low priority at least 1800 s.
 */
class DeliverySchedulerTest extends HumHubDbTestCase
{
    use DeliveryTestTrait;

    public $fixtureConfig = ['default'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDelivery();
    }

    public function testFirstMessageGoesAtOnce()
    {
        $this->dispatchToAdmin();

        $deliveries = $this->deliveries();
        $this->assertCount(1, $deliveries);
        $this->assertDueIn(0, $deliveries[0]);
        $this->assertSame(NotificationDelivery::STATE_SENT, (int)$deliveries[0]->state);
        $this->assertSame(date('Y-m-d H:i:s', $this->now), $deliveries[0]->sent_at);
        $this->assertCount(1, $this->mails());
        // one job, without delay
        $this->assertCount(1, $this->deliverJobPushes());
        $this->assertSame(0, $this->deliverJobPushes()[0]->delay);
        // no rows for the web list
        $this->assertCount(0, $this->deliveries('web'));
    }

    public function testSecondMessageWithinTheWindowWaitsFiveMinutes()
    {
        $this->dispatchToAdmin();
        $this->now += 10;
        $this->dispatchToAdmin();

        $delivery = $this->lastDelivery();
        $this->assertDueIn(300, $delivery);
        $this->assertSame(NotificationDelivery::STATE_PENDING, (int)$delivery->state);
        $this->assertCount(1, $this->mails());
        $this->assertSame(300, $this->deliverJobPushes()[1]->delay);
    }

    public function testDelaysGrowWithEveryMessage()
    {
        $this->dispatchToAdmin();

        // the 2nd message after 300 s, the 3rd after 900 s, every further one after 1800 s
        foreach ([300, 900, 1800, 1800] as $delay) {
            $this->now += 10;
            $this->dispatchToAdmin();
            $this->assertDueIn($delay, $this->lastDelivery());

            $this->now += $delay;
            $this->runDeliverJob();
            $this->assertSame(NotificationDelivery::STATE_SENT, (int)$this->lastDelivery()->state);
        }

        $this->assertCount(5, $this->mails());
    }

    public function testExpiredWindowResetsTheDelay()
    {
        $this->dispatchToAdmin();
        $this->now += 3601;
        $this->dispatchToAdmin();

        $this->assertDueIn(0, $this->lastDelivery());
        $this->assertCount(2, $this->mails());
    }

    public function testHighPriorityGoesAtOnce()
    {
        $this->dispatchToAdmin();
        $this->now += 10;
        $this->dispatchToAdmin(NotificationPriority::High);

        $this->assertDueIn(0, $this->lastDelivery());
        $this->assertSame(NotificationDelivery::STATE_SENT, (int)$this->lastDelivery()->state);
        $this->assertCount(2, $this->mails());
    }

    public function testLowPriorityWaitsAtLeastTheLowPriorityDelay()
    {
        // even as the first message in a quiet hour
        $this->dispatchToAdmin(NotificationPriority::Low);

        $this->assertDueIn(1800, $this->lastDelivery());
        $this->assertCount(0, $this->mails());
    }

    public function testRidesAlongWithAnEarlierPendingMessage()
    {
        $this->dispatchToAdmin();
        $this->now += 10;
        $this->dispatchToAdmin();
        $carrier = $this->lastDelivery();
        $this->assertCount(2, $this->deliverJobPushes());

        // a low-priority one would wait 1800 s: it takes the carrier's due time, no job of its own
        $this->now += 10;
        $this->dispatchToAdmin(NotificationPriority::Low);
        $this->assertSame($carrier->due_at, $this->lastDelivery()->due_at);
        // a normal one would wait 300 s, longer than the carrier
        $this->now += 10;
        $this->dispatchToAdmin();
        $this->assertSame($carrier->due_at, $this->lastDelivery()->due_at);
        $this->assertCount(2, $this->deliverJobPushes());
    }

    public function testDoesNotRideAlongWithALaterPendingMessage()
    {
        $this->dispatchToAdmin(NotificationPriority::Low);
        $this->dispatchToAdmin();

        // the normal one goes at once and takes the low one along
        $deliveries = $this->deliveries();
        $this->assertDueIn(1800, $deliveries[0]);
        $this->assertDueIn(0, $deliveries[1]);
        $this->assertSame(NotificationDelivery::STATE_SENT, (int)$deliveries[0]->state);
        $this->assertSame(NotificationDelivery::STATE_SENT, (int)$deliveries[1]->state);
        $mails = $this->mails();
        $this->assertCount(1, $mails);
        $this->assertSame('2 new notifications', $mails[0]->getSubject());
    }

    public function testNoCarrierDueWithinFiveSeconds()
    {
        $this->dispatchToAdmin();
        $this->now += 10;
        $this->dispatchToAdmin();
        // the pending one is due in 4 s: its job may be about to run
        $this->now += 296;
        $this->dispatchToAdmin(NotificationPriority::Low);

        $this->assertDueIn(1800, $this->lastDelivery());
        $this->assertCount(3, $this->deliverJobPushes());
    }

    public function testDisabledChannelCreatesNoRow()
    {
        (new NotificationSettingsService(User::findOne(['id' => 1])))->setCategory(new MailTarget(), TestNotification::category(), false);
        $this->dispatchToAdmin();

        $this->assertCount(0, $this->deliveries());
        $this->assertCount(0, $this->deliverJobPushes());
    }

    public function testDelaysZeroMakesTheChannelInstant()
    {
        $this->setUpDelivery(['delays' => [0]]);

        foreach ([0, 10, 20] as $offset) {
            $this->now += $offset;
            $this->dispatchToAdmin();
            $this->assertDueIn(0, $this->lastDelivery());
        }

        $this->assertCount(3, $this->mails());
    }

    public function testQueueWithoutDelayMakesEveryChannelInstant()
    {
        Yii::$app->notification->instantDelivery = null;
        // the test queue runs every job at once
        $this->assertFalse(DeliveryScheduler::queueHonoursDelay());
        $this->assertTrue(DeliveryScheduler::isInstant());

        $this->dispatchToAdmin(NotificationPriority::Low);
        $this->now += 10;
        $this->dispatchToAdmin();
        $this->now += 10;
        $this->dispatchToAdmin(NotificationPriority::Low);

        foreach ($this->deliveries() as $delivery) {
            $this->assertSame($delivery->created_at, $delivery->due_at);
            $this->assertSame(NotificationDelivery::STATE_SENT, (int)$delivery->state);
        }
        $this->assertCount(3, $this->mails());

        // a queue known to honour delays
        Yii::$app->set('queue', new DelayingTestQueue());
        $this->assertTrue(DeliveryScheduler::queueHonoursDelay());
        $this->assertFalse(DeliveryScheduler::isInstant());
        // the override
        Yii::$app->notification->instantDelivery = true;
        $this->assertTrue(DeliveryScheduler::isInstant());
    }

    public function testEndToEndTwoDispatches()
    {
        $this->dispatchToAdmin();
        $this->assertCount(1, $this->mails());

        $this->now += 10;
        $this->dispatchToAdmin();
        $this->assertCount(1, $this->mails());

        // the delayed job, early: nothing due yet
        $this->now += 299;
        $this->runDeliverJob();
        $this->assertCount(1, $this->mails());

        $this->now += 1;
        $this->runDeliverJob();
        $mails = $this->mails();
        $this->assertCount(2, $mails);
        $this->assertSame('Peter Tester did a thing', $mails[1]->getSubject());
        foreach ($this->deliveries() as $delivery) {
            $this->assertSame(NotificationDelivery::STATE_SENT, (int)$delivery->state);
        }
    }
}
