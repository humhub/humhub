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
 * Notifies a user about an invitation to a space. The source is the {@see \humhub\modules\space\models\Space}.
 *
 * Since 1.20 built on the record-bound {@see BaseNotification}, formerly `Invite` (since 0.5).
 *
 * @since 1.20
 */
final class SpaceInviteNotification extends BaseNotification
{
    use SpaceNotificationTrait;

    /**
     * @inheritdoc
     */
    public static function group(): NotificationGroup
    {
        return NotificationGroup::direct();
    }

    /**
     * The about page of the space, which an invited user may open.
     *
     * @inheritdoc
     */
    public function getUrl(bool $scheme = false): ?string
    {
        return $this->getSpace()?->createUrl('/space/space/about', [], $scheme);
    }

    /**
     * @inheritdoc
     */
    protected function getMessage(array $params): string
    {
        return Yii::t('SpaceModule.notification', '{displayName} invited you to the space {spaceName}', [
            'displayName' => $params['displayName'],
            'spaceName' => $params['spaceName'],
        ]);
    }
}
