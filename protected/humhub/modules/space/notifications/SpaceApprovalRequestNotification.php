<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\notifications;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationBlock;
use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\notification\components\NotificationContext;
use Yii;

/**
 * Notifies the administrators of a space about a membership request. The source is the
 * {@see \humhub\modules\space\models\Space}, the originator the applicant; the payload carries
 * the applicant's `message`. The notification links to the pending approvals of the space.
 *
 * Since 1.20 built on the record-bound {@see BaseNotification}, formerly `ApprovalRequest` (since 0.5).
 *
 * @since 1.20
 */
final class SpaceApprovalRequestNotification extends BaseNotification
{
    use SpaceNotificationTrait;

    /**
     * @inheritdoc
     */
    public static function category(): NotificationCategory
    {
        return NotificationCategory::direct();
    }

    /**
     * The pending approvals of the space.
     *
     * @inheritdoc
     */
    public function getUrl(bool $scheme = false): ?string
    {
        return $this->getSpace()?->createUrl('/space/manage/member/pending-approvals', [], $scheme);
    }

    /**
     * The message of the applicant, quoted.
     *
     * @inheritdoc
     */
    public function getBlocks(NotificationContext $context): array
    {
        $message = trim((string)($this->payload['message'] ?? ''));

        return $message !== '' ? [NotificationBlock::quote($message, $this->originator, $this->record->created_at)] : [];
    }

    /**
     * @inheritdoc
     */
    protected function getMessage(array $params): string
    {
        return Yii::t('SpaceModule.notification', '{displayName} requests membership for the space {spaceName}', [
            'displayName' => $params['displayName'],
            'spaceName' => $params['spaceName'],
        ]);
    }
}
