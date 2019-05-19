<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\targets;

use Yii;
use yii\base\Exception;
use humhub\modules\user\models\User;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\live\NewNotification;

/**
 * Web Target
 * 
 * @since 1.2
 * @author buddha
 */
class WebTarget extends BaseTarget
{

    /**
     * @inheritdoc
     */
    public $id = 'web';

    /**
     * @inheritdoc
     */
    public $defaultSetting = true;

    /**
     * Handles Webnotifications by setting the send_web_notifications flag and sending an live event.
     */
    public function handle(BaseNotification $notification, User $user)
    {
        if (!$notification->record) {
            throw new Exception('Notification record not found for BaseNotification "' . $notification->className() . '"');
        }

        $notification->record->send_web_notifications = true;
        $notification->record->save();

        Yii::$app->live->send(new NewNotification([
            'notificationId' => $notification->record->id,
            'notificationGroup' => ($notification->getGroupKey()) ? (get_class($notification).':'.$notification->getGroupKey()) : null,
            'contentContainerId' => $user->contentcontainer_id,
            'ts' => time(),
            'text' => $notification->text()
        ]));
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return Yii::t('NotificationModule.targets', 'Web');
    }

}
