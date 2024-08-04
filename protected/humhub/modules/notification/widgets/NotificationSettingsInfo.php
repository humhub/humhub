<?php

namespace humhub\modules\notification\widgets;

use Yii;
use humhub\widgets\BaseStack;
use humhub\modules\fcmPush\widgets\PushNotificationInfoWidget;

class NotificationSettingsInfo extends BaseStack
{
    public function run()
    {
        if (Yii::$app->hasModule('fcm-push')) {
            return PushNotificationInfoWidget::widget();
        }
        return;
    }
}
