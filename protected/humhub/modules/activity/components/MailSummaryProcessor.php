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
        $users = User::find()->distinct()->joinWith(['httpSessions', 'profile'])->where([
            'user.status' => User::STATUS_ENABLED
        ]);

        $interactive = false;
        $totalUsers = $users->count();
        $processed = 0;
        $mailsSent = 0;

        if ($interval == MailSummary::INTERVAL_DAILY) {
            if ($interactive) {
                Console::startProgress($processed, $totalUsers, 'Sending daily e-mail summary to users... ', false);
            }
        } elseif ($interval === MailSummary::INTERVAL_HOURY) {
            if ($interactive) {
                Console::startProgress($processed, $totalUsers, 'Sending hourly e-mail summary to users... ', false);
            }
        } elseif ($interval === MailSummary::INTERVAL_WEEKLY) {
            if ($interactive) {
                Console::startProgress($processed, $totalUsers, 'Sending weekly e-mail summary to users... ', false);
            }
        } else {
            return;
        }

        foreach ($users->each() as $user) {

            // Check if user wants summary in the given interval
            try {
                if (self::checkUser($user, $interval)) {
                    $mailSummary = Yii::createObject([
                                'class' => MailSummary::className(),
                                'user' => $user,
                                'interval' => $interval
                    ]);
                    if ($mailSummary->send()) {
                        $mailsSent++;
                    }
                }
            } catch (\Exception $ex) {
                Yii::error('Could not send activity mail to: ' . $user->displayName . ' (' . $ex->getMessage() . ')', 'activity');
            }
            if ($interactive) {
                Console::updateProgress( ++$processed, $totalUsers);
            }
        }

        if ($interactive) {
            Console::endProgress(true);
            Yii::$app->controller->stdout('done - ' . $mailsSent . ' email(s) sent.' . PHP_EOL, Console::FG_GREEN);
        }
    }

    /**
     * Checks if a e-mail summary should be send to the user
     *
     * @param User $user
     * @param int $interval
     */
    protected static function checkUser(User $user, $interval)
    {
        if (empty($user->email)) {
            return false;
        }

        $activityModule = Yii::$app->getModule('activity');
        $defaultInterval = (int) $activityModule->settings->get('mailSummaryInterval', MailSummary::INTERVAL_DAILY);
        $wantedInterval = (int) $activityModule->settings->user($user)->get('mailSummaryInterval', $defaultInterval);

        if ($interval !== $wantedInterval) {
            return false;
        }

        return true;
    }

}
