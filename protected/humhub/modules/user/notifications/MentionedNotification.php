<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\notifications;

use humhub\modules\content\interfaces\ContentOwner;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationCategory;
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
    public static function category(): NotificationCategory
    {
        return NotificationCategory::direct();
    }

    /**
     * The record the user is mentioned in: a comment, else the content's record.
     *
     * @inheritdoc
     */
    public function getSubjectRecord(): ?ContentOwner
    {
        return $this->sourceRecord instanceof ContentOwner ? $this->sourceRecord : parent::getSubjectRecord();
    }

    /**
     * @inheritdoc
     */
    protected function getMessage(array $params): string
    {
        return Yii::t('UserModule.notification', '{displayName} mentioned you in {content}.', $params);
    }

    /**
     * @inheritdoc
     */
    protected function getMailSubject(array $params): string
    {
        $record = $this->getSubjectRecord();
        if ($record === null) {
            return parent::getMailSubject($params);
        }

        return Yii::t('UserModule.notification', '{displayName} just mentioned you in {contentTitle} "{preview}"', [
            'displayName' => $params['displayName'],
            'contentTitle' => $record->getContentName(),
            'preview' => $params['contentTitle'],
        ]);
    }
}
