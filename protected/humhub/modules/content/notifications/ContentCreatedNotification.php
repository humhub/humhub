<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\notifications;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\Grouping;
use humhub\modules\notification\components\NotificationCategory;
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
    public static function category(): NotificationCategory
    {
        return NotificationCategory::content();
    }

    /**
     * The new contents of one type, by one originator, in one container.
     *
     * @inheritdoc
     */
    public static function grouping(): ?Grouping
    {
        return Grouping::byContainer()->andOriginator()->andContentType();
    }

    /**
     * @inheritdoc
     */
    protected function getMessage(array $params): string
    {
        if ($this->groupCount === 1 && $this->isOnRecipientProfile()) {
            return Yii::t('ContentModule.notifications', '{displayName} posted on your profile {contentTitle}.', $params);
        }

        return Yii::t('ContentModule.notifications', '{groupCount, plural, =1{{displayName} created {content}.} other{{displayName} created # new entries.}}', $params);
    }

    /**
     * @inheritdoc
     */
    protected function getMailSubject(array $params): string
    {
        if ($this->groupCount > 1) {
            return parent::getMailSubject($params);
        }

        $explicit = (bool)($this->payload['explicit'] ?? false);
        $space = $this->getSpace();
        $params = [
            'originator' => $params['displayName'],
            'contentInfo' => $params['content'] ?? '',
            'space' => $space?->displayName,
        ];

        if ($space !== null) {
            return $explicit
                ? Yii::t('ContentModule.notifications', '{originator} notifies you about {contentInfo} in {space}', $params)
                : Yii::t('ContentModule.notifications', '{originator} just wrote {contentInfo} in Space {space}', $params);
        }

        return $explicit
            ? Yii::t('ContentModule.notifications', '{originator} notifies you about {contentInfo}', $params)
            : Yii::t('ContentModule.notifications', '{originator} just wrote {contentInfo}', $params);
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
