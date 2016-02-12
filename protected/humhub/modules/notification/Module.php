<?php

namespace humhub\modules\notification;

use humhub\modules\user\models\User;
use humhub\modules\notification\models\Notification;
use humhub\modules\notification\components\BaseNotification;
use humhub\models\Setting;
use humhub\commands\CronController;

/**
 * NotificationModule
 *
 * @package humhub.modules_core.notification
 * @since 0.5
 */
class Module extends \humhub\components\Module
{

    public function getMailUpdate(User $user, $interval)
    {
        $output = ['html' => '', 'plaintext' => ''];

        
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
        

        $query = Notification::find()->where(['user_id' => $user->id])->andWhere(['!=', 'seen', 1])->andWhere(['!=', 'emailed', 1]);
        foreach ($query->all() as $notification) {
            $output['html'] .= $notification->getClass()->render(BaseNotification::OUTPUT_MAIL);
            $output['plaintext'] .= $notification->getClass()->render(BaseNotification::OUTPUT_MAIL_PLAINTEXT);


            $notification->emailed = 1;
            $notification->save();
        }

        return $output;
    }

}
