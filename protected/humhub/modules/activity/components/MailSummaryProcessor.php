<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\components;

use Yii;
use yii\helpers\Console;
use humhub\modules\activity\components\MailSummary;
use humhub\modules\user\models\User;

/**
 * MailSummaryProcessor is called by cron on given intervals (daily or hourly)
 * and creates mail summaries from the users.
 *
 * @since 1.2
 * @author Luke
 */
class MailSummaryProcessor
{

    /**
     * Processes mail summary for given interval
     * 
     * @param int $interval
     */
    public static function process($interval)
    {
        $users = User::find()->distinct()->joinWith(['httpSessions', 'profile'])->where(['user.status' => User::STATUS_ENABLED]);

        $totalUsers = $users->count();
        $processed = 0;

        Console::startProgress($processed, $totalUsers, 'Sending update e-mails to users... ', false);

        $mailsSent = 0;
        foreach ($users->each() as $user) {

            // Check if user wants summary in the given interval
            if (self::shouldSendToUser($user, $interval)) {
                $mailSummary = Yii::configure(MailSummary::className(), ['user' => $user, 'interval' => $interval]);
                if ($mailSummary->send()) {
                    $mailsSent++;
                }
            }

            Console::updateProgress(++$processed, $totalUsers);
        }

        Console::endProgress(true);
        Yii::$app->controller->stdout('done - ' . $mailsSent . ' email(s) sent.' . PHP_EOL, Console::FG_GREEN);
    }

    /**
     * Checks if a e-mail summary should be send to the user
     * 
     * @param User $user
     * @param int $interval
     */
    protected static function shouldSendToUser(User $user, $interval)
    {
        if (empty($user->email)) {
            return false;
        }

        $activityModule = Yii::$app->getModule('activity');
        $defaultInterval = $activityModule->settings->get('mailSummaryInterval', MailSummary::INTERVAL_DAILY);
        $wantedInterval = $activityModule->settings->user($user)->get('mailSummaryInterval', $defaultInterval);

        if ($interval !== $wantedInterval) {
            return false;
        }

        return true;
    }

}
