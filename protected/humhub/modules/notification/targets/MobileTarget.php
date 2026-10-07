<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\targets;

use humhub\modules\user\models\User;
use Yii;
use yii\di\NotInstantiableException;

/**
 * Mobile push notifications, sent by a {@see MobileTargetProvider} a module (e.g. fcm-push)
 * registers in the DI container. Without a provider the target is not active.
 *
 * @since 1.2, rewritten in 1.20
 */
class MobileTarget extends BaseTarget
{
    /**
     * @inheritdoc
     */
    public string $id = 'mobile';

    /**
     * @inheritdoc
     */
    public array $modes = [self::MODE_ADAPTIVE, self::MODE_OFF];

    /**
     * @var MobileTargetProvider|null the push provider; from the DI container unless configured
     */
    public ?MobileTargetProvider $provider = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        try {
            $this->provider ??= Yii::$container->get(MobileTargetProvider::class);
        } catch (NotInstantiableException) {
            // No provider installed
        }
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): string
    {
        return Yii::t('NotificationModule.targets', 'Mobile');
    }

    /**
     * @inheritdoc
     */
    public function deliver(DeliveryBatch $batch): void
    {
        $this->provider?->deliver($batch);
    }

    /**
     * @inheritdoc
     */
    public function isActive(?User $user = null): bool
    {
        return parent::isActive($user) && $this->provider !== null && $this->provider->isActive($user);
    }
}
