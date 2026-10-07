<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\notifications;

use humhub\modules\notification\components\ActiveQueryNotification;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationGroup;
use humhub\modules\notification\models\Notification;
use humhub\modules\user\models\Follow;
use Yii;

/**
 * Notifies a user that someone follows them. The source is the {@see Follow} record. The new
 * followers of a user are grouped.
 *
 * Since 1.20 built on the record-bound {@see BaseNotification}, formerly `Followed`.
 *
 * @since 1.20
 */
final class FollowedNotification extends BaseNotification
{
    /**
     * @inheritdoc
     */
    public static function group(): NotificationGroup
    {
        return NotificationGroup::social();
    }

    /**
     * @inheritdoc
     */
    public function getGroupingQuery(): ?ActiveQueryNotification
    {
        return Notification::find()->andWhere(['notification.class' => self::class]);
    }

    /**
     * The profile of the follower.
     *
     * @inheritdoc
     */
    public function getUrl(bool $scheme = false): ?string
    {
        return $this->originator?->getUrl($scheme);
    }

    /**
     * @inheritdoc
     */
    protected function getMessage(array $params): string
    {
        if ($params['namedCount'] > 1) {
            return Yii::t('UserModule.notification', '{displayNames} are now following you.', [
                'displayNames' => $params['displayNames'],
            ]);
        }

        return Yii::t('UserModule.notification', '{displayName} is now following you.', [
            'displayName' => $params['namedCount'] === 1 ? $params['displayNames'] : $params['displayName'],
        ]);
    }
}
