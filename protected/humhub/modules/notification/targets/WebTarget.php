<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\targets;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\user\models\User;
use Yii;

/**
 * The web list of notifications - always on.
 *
 * The list is written by the dispatch job ({@see \humhub\modules\notification\jobs\DispatchJob}),
 * which stores every notification and sends the live event for those
 * {@see BaseNotification::listed()}. This target delivers nothing; it exists as the id behind
 * `listed()` and for the settings page.
 *
 * @since 1.2, rewritten in 1.20
 */
class WebTarget extends BaseTarget
{
    /**
     * @inheritdoc
     */
    public string $id = 'web';

    /**
     * @inheritdoc
     */
    public array $modes = [self::MODE_ADAPTIVE];

    /**
     * @inheritdoc
     */
    public function getTitle(): string
    {
        return Yii::t('NotificationModule.targets', 'Web');
    }

    /**
     * Nothing to do: the web list is written by the dispatch job.
     *
     * @inheritdoc
     */
    public function deliver(DeliveryBatch $batch): void
    {
    }

    /**
     * Whether the class appears in the web list - there are no settings for this channel.
     *
     * @inheritdoc
     */
    public function isEnabled(string $notificationClass, ?User $user = null): bool
    {
        return $this->isActive($user) && $notificationClass::listed();
    }
}
