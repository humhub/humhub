<?php

namespace humhub\modules\notification\components;

use Yii;
use humhub\modules\user\models\User;

/**
 *
 * @author buddha
 */
class WebNotificationTarget extends NotificationTarget
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
            throw new \yii\base\Exception('Notification record not found for BaseNotification "' . $notification->className() . '"');
        }

        $notification->record->send_web_notifications = true;
        $notification->record->save();

        Yii::$app->live->send(new \humhub\modules\notification\live\NewNotification([
            'notificationId' => $notification->record->id,
            'contentContainerId' => $user->contentcontainer_id,
            'ts' => time(),
            'text' => $notification->text()
        ]));
    }

    public function getTitle()
    {
        return Yii::t('NotificationModule.components_WebNotificationTarget', 'Web');
    }

}
