<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\notifications;

use humhub\modules\content\interfaces\ContentOwner;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationGroup;
use Yii;

/**
 * Notifies a user that someone mentioned them. The source is the record the user is mentioned
 * in: a content record (e.g. a post) or a content addon (e.g. a comment).
 *
 * The sentence names and the mail previews the record the user is mentioned in; the
 * notification links to it.
 *
 * Since 1.20 built on the record-bound {@see BaseNotification}, formerly `Mentioned`.
 *
 * @since 1.20
 */
final class MentionedNotification extends BaseNotification
{
    /**
     * @inheritdoc
     */
    public static function group(): NotificationGroup
    {
        return NotificationGroup::direct();
    }

    /**
     * The record the user is mentioned in: a comment, else the content's record.
     *
     * @inheritdoc
     */
    protected function getContentOwner(): ?ContentOwner
    {
        return $this->sourceRecord instanceof ContentOwner ? $this->sourceRecord : parent::getContentOwner();
    }

    /**
     * @inheritdoc
     */
    protected function getMessage(array $params): string
    {
        return Yii::t('UserModule.notification', '{displayName} mentioned you in {contentTitle}.', [
            'displayName' => $params['displayName'],
            'contentTitle' => $params['content'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getMailSubject(): string
    {
        $owner = $this->getContentOwner();
        if ($owner === null) {
            return parent::getMailSubject();
        }

        $params = $this->getMessageParamsPlain($this->webContentLength);

        return Yii::t('UserModule.notification', '{displayName} just mentioned you in {contentTitle} "{preview}"', [
            'displayName' => $params['displayName'],
            'contentTitle' => $owner->getContentName(),
            'preview' => $params['contentTitle'],
        ]);
    }
}
