<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\unit;

use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\notification\services\NotificationSettingsService;
use humhub\modules\notification\targets\MailTarget;
use humhub\modules\notification\targets\DeliveryBatch;
use humhub\modules\notification\targets\MobileTarget;
use humhub\modules\notification\targets\MobileTargetProvider;
use humhub\modules\notification\targets\WebTarget;
use humhub\modules\notification\tests\codeception\unit\notifications\TestDirectNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestHighPriorityNotification;
use humhub\modules\notification\tests\codeception\unit\notifications\TestNotification;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\models\Notification;
use humhub\modules\post\models\Post;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\InvalidConfigException;

class BaseTargetTest extends HumHubDbTestCase
{
    public $fixtureConfig = ['default'];

    public function testMailTargetIsEnabledByDefault()
    {
        $this->assertTrue((new MailTarget())->isEnabled(TestNotification::class, $this->user()));
        $this->assertTrue((new MailTarget())->isEnabled(TestNotification::class));
    }

    public function testInactiveTargetIsDisabled()
    {
        $this->assertFalse((new MailTarget(['active' => false]))->isEnabled(TestNotification::class, $this->user()));
        $this->assertFalse((new MailTarget(['active' => false]))->isEnabled(TestDirectNotification::class, $this->user()));
    }

    public function testMailTargetIsDisabledByCategorySwitch()
    {
        (new NotificationSettingsService($this->user()))->setCategory(new MailTarget(), NotificationCategory::social(), false);
        $this->assertFalse((new MailTarget())->isEnabled(TestNotification::class, $this->user()));
        $this->assertTrue((new MailTarget())->isEnabled(TestDirectNotification::class, $this->user()));
        $this->assertTrue((new MailTarget())->isEnabled(TestNotification::class, User::findOne(['id' => 2])));
    }

    public function testDirectCategoryIgnoresItsSwitch()
    {
        Yii::$app->getModule('notification')->settings->user($this->user())->set('email.category.direct', 0);
        $this->assertTrue((new MailTarget())->isEnabled(TestDirectNotification::class, $this->user()));
    }

    public function testWebTargetFollowsListedAndTheCategorySwitch()
    {
        $this->assertTrue((new WebTarget())->isEnabled(TestNotification::class, $this->user()));
        $this->assertFalse((new WebTarget())->isEnabled(TestHighPriorityNotification::class, $this->user()));

        (new NotificationSettingsService($this->user()))->setCategory(new WebTarget(), NotificationCategory::social(), false);
        $this->assertFalse((new WebTarget())->isEnabled(TestNotification::class, $this->user()));
        $this->assertTrue((new WebTarget())->isEnabled(TestNotification::class, User::findOne(['id' => 2])));
        // the direct category cannot be switched off
        Yii::$app->getModule('notification')->settings->user($this->user())->set('web.category.direct', 0);
        $this->assertTrue((new WebTarget())->isEnabled(TestDirectNotification::class, $this->user()));
    }

    public function testWebTargetAppliesToCategoriesWithListedNotifications()
    {
        $this->assertTrue((new WebTarget())->appliesTo(NotificationCategory::social()));
        $this->assertTrue((new WebTarget())->appliesTo(NotificationCategory::followers()));
        $this->assertFalse((new WebTarget())->appliesTo(new NotificationCategory('example-none', 'None')));
        $this->assertTrue((new MailTarget())->appliesTo(new NotificationCategory('example-none', 'None')));
    }

    public function testMobileTargetWithoutProvider()
    {
        $target = new MobileTarget();
        $this->assertNull($target->provider);
        $this->assertFalse($target->isActive());
        $this->assertFalse($target->isActive($this->user()));
        $this->assertFalse($target->isEnabled(TestDirectNotification::class, $this->user()));
    }

    public function testTargetWithoutIdIsRejected()
    {
        $this->expectException(InvalidConfigException::class);
        new MailTarget(['id' => '']);
    }

    public function testMobileTargetForwardsToTheProvider()
    {
        $provider = new class implements MobileTargetProvider {
            /** @var DeliveryBatch[] */
            public array $batches = [];

            public function deliver(DeliveryBatch $batch): void
            {
                $this->batches[] = $batch;
            }

            public function isActive(?User $user = null): bool
            {
                return true;
            }
        };

        Yii::$container->set(MobileTargetProvider::class, $provider);
        try {
            // a fresh manager, the application's has instantiated its targets already
            Yii::$app->set('notification', ['class' => NotificationManager::class, 'targets' => Yii::$app->notification->targets]);
            $this->assertSame(['web', 'email', 'mobile'], array_map(fn($t) => $t->id, Yii::$app->notification->getTargets()));

            TestDirectNotification::send([1], Post::findOne(['id' => 2]), User::findOne(['id' => 2]));

            $this->assertCount(1, $provider->batches);
            $batch = $provider->batches[0];
            $this->assertSame('mobile:1:' . (int)$batch->first()->record->grouping_key, $batch->getCollapseKey());
            $this->assertSame(1, (int)$batch->recipient->id);
            $this->assertSame(
                (int)Notification::findOne(['class' => TestDirectNotification::class, 'user_id' => 1])->id,
                (int)$batch->first()->record->id,
            );
            $this->assertTrue($batch->isHighPriority());
        } finally {
            Yii::$container->clear(MobileTargetProvider::class);
        }
    }

    public function testConfiguredProviderIsKept()
    {
        $provider = new class implements MobileTargetProvider {
            public function deliver(DeliveryBatch $batch): void
            {
            }

            public function isActive(?User $user = null): bool
            {
                return true;
            }
        };

        $target = new MobileTarget(['provider' => $provider]);
        $this->assertSame($provider, $target->provider);
        $this->assertTrue($target->isActive());
    }

    public function testThroughTheManager()
    {
        $target = Yii::$app->notification->getTarget('email');
        $this->assertInstanceOf(MailTarget::class, $target);
        $this->assertTrue($target->isEnabled(TestNotification::class, $this->user()));

        (new NotificationSettingsService($this->user()))->setCategory($target, TestNotification::category(), false);
        $this->assertFalse($target->isEnabled(TestNotification::class, $this->user()));
        $this->assertTrue($target->isEnabled(TestNotification::class, User::findOne(['id' => 2])));
    }

    private function user(): User
    {
        return User::findOne(['id' => 1]);
    }
}
