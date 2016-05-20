<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification;

use Yii;
use humhub\modules\user\models\User;
use humhub\modules\notification\models\Notification;
use humhub\modules\content\components\MailUpdateSender;

/**
 * Notification Module
 */
class Module extends \humhub\components\Module
{

    /**
     * @var int the seconds the browser checks for new notifications
     */
    public $pollClientUpdateInterval = 20;

    /**
     * Returns all notifications which should be send by e-mail to the given user
     * in the given interval
     *
     * @see \humhub\modules\content\components\MailUpdateSender
     * @param User $user
     * @param int $interval
     * @return components\BaseNotification[]
     */
    public function getMailNotifications(User $user, $interval)
    {
        $notifications = [];

        $receive_email_notifications = Yii::$app->getModule('notification')->settings->contentContainer($user)->get('receive_email_notifications');
        if ($receive_email_notifications === null) {
            // Use Default
            $receive_email_notifications = Yii::$app->getModule('notification')->settings->get('receive_email_notifications');
        }

        // Never receive notifications
        if ($receive_email_notifications == User::RECEIVE_EMAIL_NEVER) {
            return [];
        }

        // We are in hourly mode and user wants daily
        if ($interval == MailUpdateSender::INTERVAL_HOURY && $receive_email_notifications == User::RECEIVE_EMAIL_DAILY_SUMMARY) {
            return [];
        }

        // We are in daily mode and user dont wants daily reports
        if ($interval == MailUpdateSender::INTERVAL_DAILY && $receive_email_notifications != User::RECEIVE_EMAIL_DAILY_SUMMARY) {
            return [];
        }

        // User wants only when offline and is online
        if ($interval == MailUpdateSender::INTERVAL_HOURY) {
            $isOnline = (count($user->httpSessions) > 0);
            if ($receive_email_notifications == User::RECEIVE_EMAIL_WHEN_OFFLINE && $isOnline) {
                return [];
            }
        }

        $query = Notification::findGrouped()->andWhere(['user_id' => $user->id])->andWhere(['!=', 'seen', 1])->andWhere(['!=', 'emailed', 1]);
        foreach ($query->all() as $notification) {
            $notifications[] = $notification->getClass();

            // Mark notifications as mailed
            $notification->emailed = 1;
            $notification->save();
        }

        return $notifications;
    }

}
