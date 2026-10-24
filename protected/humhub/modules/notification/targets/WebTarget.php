<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\targets;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\user\models\User;
use Throwable;
use Yii;

/**
 * The web list of notifications.
 *
 * The list is written by the dispatch job ({@see \humhub\modules\notification\jobs\DispatchJob}):
 * it stores a notification as `listed` - shown in the list and counted in the badge, with a live
 * event - when this channel is enabled for it ({@see isEnabled()}), and unlisted when only other
 * channels deliver it. This target delivers nothing itself.
 *
 * Only notification classes that are {@see BaseNotification::listed()} appear in the list; a
 * category without such a class has no web switch ({@see appliesTo()}).
 *
 * @since 1.2, rewritten in 1.20
 */
final class WebTarget extends BaseTarget
{
    public const ID = 'web';

    /**
     * @inheritdoc
     */
    public string $id = self::ID;

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
     * Whether the class appears in the web list at all, and its category is switched on for it.
     *
     * @inheritdoc
     */
    public function isEnabled(string $notificationClass, ?User $user = null): bool
    {
        return $notificationClass::listed() && parent::isEnabled($notificationClass, $user);
    }

    /**
     * Only a category with a notification class that is {@see BaseNotification::listed()}.
     *
     * @inheritdoc
     */
    public function appliesTo(NotificationCategory $category): bool
    {
        foreach (Yii::$app->notification->getNotifications() as $class) {
            try {
                if ($class::listed() && $class::category()->equals($category)) {
                    return true;
                }
            } catch (Throwable) {
                // reported by NotificationManager::getCategories()
            }
        }

        return false;
    }
}
