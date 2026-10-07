<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\notifications;

use humhub\helpers\Html;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationGroup;
use humhub\modules\notification\components\NotificationPriority;
use humhub\modules\user\models\Group;
use Yii;
use yii\helpers\Url;

/**
 * Notifies a user that they were added to a group. The source is the {@see Group}, the originator
 * the user who changed the membership - none when it was changed by the system.
 *
 * Since 1.20 on the record-bound {@see BaseNotification}.
 *
 * @since 1.3
 */
final class IncludeGroupNotification extends BaseNotification
{
    /**
     * @inheritdoc
     */
    public static function group(): NotificationGroup
    {
        return NotificationGroup::direct();
    }

    /**
     * @inheritdoc
     */
    public static function priority(): NotificationPriority
    {
        return NotificationPriority::Normal;
    }

    /**
     * The people directory.
     *
     * @inheritdoc
     */
    public function getUrl(bool $scheme = false): ?string
    {
        return Url::to(['/user/people'], $scheme);
    }

    /**
     * @inheritdoc
     */
    protected function getMessage(array $params): string
    {
        if ($this->originator === null) {
            return Yii::t('AdminModule.notification', 'You were added to the group {groupName}', [
                'groupName' => $params['groupName'],
            ]);
        }

        return Yii::t('AdminModule.notification', '{displayName} added you to group {groupName}', [
            'displayName' => $params['displayName'],
            'groupName' => $params['groupName'],
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function getMessageParamsPlain(int $maxLength): array
    {
        return array_merge(parent::getMessageParamsPlain($maxLength), [
            'groupName' => $this->getGroupName(),
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function getMessageParamsWeb(): array
    {
        return array_merge(parent::getMessageParamsWeb(), [
            'groupName' => Html::strong(Html::encode($this->getGroupName())),
        ]);
    }

    private function getGroupName(): string
    {
        return $this->sourceRecord instanceof Group ? (string)$this->sourceRecord->name : '';
    }
}
