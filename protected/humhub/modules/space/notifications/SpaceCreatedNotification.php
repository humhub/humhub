<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\notifications;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationGroup;
use Yii;

/**
 * Notifies the users who may manage spaces that a user without that permission created a space.
 * The source is the new {@see \humhub\modules\space\models\Space}, the originator its creator.
 *
 * Since 1.20 built on the record-bound {@see BaseNotification}, formerly `SpaceCreated` (since 1.16).
 *
 * @since 1.20
 */
final class SpaceCreatedNotification extends BaseNotification
{
    use SpaceNotificationTrait;

    /**
     * @inheritdoc
     */
    public static function group(): NotificationGroup
    {
        return NotificationGroup::admin();
    }

    /**
     * @inheritdoc
     */
    protected function getMessage(array $params): string
    {
        return Yii::t('SpaceModule.notification', '{displayName} created the new Space {spaceName}', [
            'displayName' => $params['displayName'],
            'spaceName' => $params['spaceName'],
        ]);
    }
}
