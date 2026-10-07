<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\notifications;

use humhub\modules\notification\components\ActiveQueryNotification;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationGroup;
use humhub\modules\notification\models\Notification;
use humhub\modules\user\models\User;
use Yii;

/**
 * Notifies the followers of a container, and the users picked in the content form, about a new
 * content. The source is the content record (e.g. a post); the payload flag `explicit` marks the
 * notification of a user picked in the content form.
 *
 * New contents of the same type, by the same originator and in the same container are grouped.
 *
 * Since 1.20 built on the record-bound {@see BaseNotification}, formerly `ContentCreated`.
 *
 * @since 1.20
 */
class ContentCreatedNotification extends BaseNotification
{
    /**
     * @inheritdoc
     */
    public static function group(): NotificationGroup
    {
        return NotificationGroup::content();
    }

    /**
     * @inheritdoc
     */
    public function getGroupingQuery(): ?ActiveQueryNotification
    {
        if ($this->content === null) {
            return null;
        }

        return Notification::find()
            ->select('notification.*')
            ->innerJoin('content', 'content.id = notification.content_id')
            ->andWhere(['notification.class' => static::class])
            ->andWhere(['notification.originator_id' => $this->record->originator_id])
            ->andWhere(['notification.contentcontainer_id' => $this->record->contentcontainer_id])
            ->andWhere(['content.object_model' => $this->content->object_model]);
    }

    /**
     * @inheritdoc
     */
    protected function getMessage(array $params): string
    {
        if ($this->groupCount > 1) {
            return Yii::t('ContentModule.notifications', '{displayName} created {groupCount} new entries.', [
                'displayName' => $params['displayName'],
                'groupCount' => $params['groupCount'],
            ]);
        }

        if ($this->isOnRecipientProfile()) {
            return Yii::t('ContentModule.notifications', '{displayName} posted on your profile {contentTitle}.', [
                'displayName' => $params['displayName'],
                'contentTitle' => $params['contentTitle'],
            ]);
        }

        return Yii::t('ContentModule.notifications', '{displayName} created {contentTitle}.', [
            'displayName' => $params['displayName'],
            'contentTitle' => $params['content'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getMailSubject(): string
    {
        if ($this->groupCount > 1) {
            return parent::getMailSubject();
        }

        $params = $this->getMessageParamsPlain($this->webContentLength);
        $contentInfo = $params['content'];
        $explicit = (bool)($this->payload['explicit'] ?? false);
        $space = $this->getSpace();

        if ($space !== null) {
            if ($explicit) {
                return Yii::t('ContentModule.notifications', '{originator} notifies you about {contentInfo} in {space}', [
                    'originator' => $params['displayName'],
                    'space' => $space->displayName,
                    'contentInfo' => $contentInfo,
                ]);
            }

            return Yii::t('ContentModule.notifications', '{originator} just wrote {contentInfo} in Space {space}', [
                'originator' => $params['displayName'],
                'space' => $space->displayName,
                'contentInfo' => $contentInfo,
            ]);
        }

        if ($explicit) {
            return Yii::t('ContentModule.notifications', '{originator} notifies you about {contentInfo}', [
                'originator' => $params['displayName'],
                'contentInfo' => $contentInfo,
            ]);
        }

        return Yii::t('ContentModule.notifications', '{originator} just wrote {contentInfo}', [
            'originator' => $params['displayName'],
            'contentInfo' => $contentInfo,
        ]);
    }

    /**
     * Whether the content was posted on the recipient's own profile.
     */
    private function isOnRecipientProfile(): bool
    {
        $container = $this->contentContainer?->polymorphicRelation;

        return $container instanceof User && (int)$container->id === (int)$this->recipient->id;
    }
}
