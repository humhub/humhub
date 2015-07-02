<?php

namespace humhub\core\notification;

use humhub\core\user\models\User;
use humhub\core\notification\models\Notification;
use humhub\core\notification\components\BaseNotification;
use humhub\models\Setting;
use humhub\commands\CronController;

/**
 * NotificationModule
 *
 * @package humhub.modules_core.notification
 * @since 0.5
 */
class Module extends \yii\base\Module
{

    public function getMailUpdate(User $user, $interval)
    {
        $output = "";

        $receive_email_notifications = $user->getSetting("receive_email_notifications", 'core', Setting::Get('receive_email_notifications', 'mailing'));

        // Never receive notifications
        if ($receive_email_notifications == User::RECEIVE_EMAIL_NEVER) {
            return "";
        }

        // We are in hourly mode and user wants daily
        if ($interval == CronController::EVENT_ON_HOURLY_RUN && $receive_email_notifications == User::RECEIVE_EMAIL_DAILY_SUMMARY) {
            return "";
        }

        // We are in daily mode and user dont wants daily reports
        if ($interval == CronController::EVENT_ON_DAILY_RUN && $receive_email_notifications != User::RECEIVE_EMAIL_DAILY_SUMMARY) {
            return "";
        }

        // User wants only when offline and is online
        if ($interval == CronController::EVENT_ON_HOURLY_RUN) {
            $isOnline = (count($user->httpSessions) > 0);
            if ($receive_email_notifications == User::RECEIVE_EMAIL_WHEN_OFFLINE && $isOnline) {
                return "";
            }
        }

        $query = Notification::find()->where(['user_id' => $user->id])->andWhere(["!=", 'seen', 1]);

        foreach ($query->all() as $notification) {
            $output .= $notification->getClass()->render(BaseNotification::OUTPUT_MAIL);

            $notification->emailed = 1;
            $notification->save();
        }

        return $output;
    }

}
