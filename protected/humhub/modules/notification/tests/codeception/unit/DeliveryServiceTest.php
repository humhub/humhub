<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use humhub\modules\notification\components\NotificationPriority;
use humhub\modules\notification\models\NotificationDelivery;
use humhub\modules\notification\services\DeliveryService;
use tests\codeception\_support\HumHubDbTestCase;

class DeliveryServiceTest extends HumHubDbTestCase
{
    use DeliveryTestTrait;

    public $fixtureConfig = ['default'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDelivery();
    }

    public function testSweepRequeuesOverduePendingDeliveries()
    {
        // pending for 1800 s, its job lost
        $this->dispatchToAdmin(NotificationPriority::Low);
        $this->pushes = [];

        // due, but not overdue by more than five minutes: the job may still run
        $this->now += 1800 + 300;
        $this->assertSame(0, (new DeliveryService())->sweep());
        $this->assertCount(0, $this->mails());

        $this->now += 1;
        $this->assertSame(1, (new DeliveryService())->sweep());
        $this->assertCount(1, $this->deliverJobPushes());
        // the test queue ran the job at once
        $this->assertCount(1, $this->mails());
        $this->assertSame(NotificationDelivery::STATE_SENT, (int)$this->lastDelivery()->state);
    }

    public function testSweepDeletesOldFinishedRows()
    {
        $this->dispatchToAdmin(NotificationPriority::Low);
        $notificationId = $this->lastDelivery()->notification_id;
        $old = date('Y-m-d H:i:s', $this->now - 31 * 86400);
        $recent = date('Y-m-d H:i:s', $this->now - 29 * 86400);
        foreach ([
            [NotificationDelivery::STATE_SENT, $old],
            [NotificationDelivery::STATE_SKIPPED, $old],
            [NotificationDelivery::STATE_FAILED, $old],
            [NotificationDelivery::STATE_SENT, $recent],
        ] as [$state, $createdAt]) {
            (new NotificationDelivery([
                'notification_id' => $notificationId,
                'user_id' => 1,
                'channel' => 'email',
                'state' => $state,
                'due_at' => $createdAt,
                'sent_at' => $state === NotificationDelivery::STATE_SENT ? $createdAt : null,
                'created_at' => $createdAt,
            ]))->save();
        }
        // a pending row stays, however old
        NotificationDelivery::updateAll(['created_at' => $old], ['notification_id' => $notificationId, 'state' => NotificationDelivery::STATE_PENDING]);
        $this->assertCount(5, $this->deliveries());

        (new DeliveryService())->sweep();

        $this->assertSame(
            [[NotificationDelivery::STATE_PENDING, $old], [NotificationDelivery::STATE_SENT, $recent]],
            array_map(fn(NotificationDelivery $d) => [(int)$d->state, $d->created_at], $this->deliveries()),
        );
    }
}
