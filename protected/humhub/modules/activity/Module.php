<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity;

use Yii;
use humhub\modules\user\models\User;
use humhub\modules\content\components\MailUpdateSender;

/**
 * Activity BaseModule
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @since 0.5
 */
class Module extends \humhub\components\Module
{

    /**
     * Returns all activities which should be send by e-mail to the given user
     * in the given interval
     *
     * @see \humhub\modules\content\components\MailUpdateSender
     * @param User $user
     * @param int $interval
     * @return components\BaseActivity[]
     */
    public function getMailActivities(User $user, $interval)
    {
        $receive_email_activities = Yii::$app->getModule('activity')->settings->contentContainer($user)->get('receive_email_activities');
        if ($receive_email_activities === null) {
            // Use Default Setting
            $receive_email_activities = Yii::$app->getModule('activity')->settings->get('receive_email_activities');
        }
        
        // User never wants activity content
        if ($receive_email_activities == User::RECEIVE_EMAIL_NEVER) {
            return [];
        }

        // We are in hourly mode and user wants receive a daily summary
        if ($interval == MailUpdateSender::INTERVAL_HOURY && $receive_email_activities == User::RECEIVE_EMAIL_DAILY_SUMMARY) {
            return [];
        }

        // We are in daily mode and user wants receive not daily
        if ($interval == MailUpdateSender::INTERVAL_DAILY && $receive_email_activities != User::RECEIVE_EMAIL_DAILY_SUMMARY) {
            return [];
        }

        // User is online and want only receive when offline
        if ($interval == MailUpdateSender::INTERVAL_HOURY) {
            $isOnline = (count($user->httpSessions) > 0);
            if ($receive_email_activities == User::RECEIVE_EMAIL_WHEN_OFFLINE && $isOnline) {
                return [];
            }
        }

        $lastMailDate = $user->last_activity_email;
        if ($lastMailDate == "" || $lastMailDate == "0000-00-00 00:00:00") {
            $lastMailDate = new \yii\db\Expression('NOW() - INTERVAL 24 HOUR');
        }

        $stream = new \humhub\modules\dashboard\components\actions\DashboardStream('stream', Yii::$app->controller);
        $stream->limit = 50;
        $stream->mode = \humhub\modules\content\components\actions\Stream::MODE_ACTIVITY;
        $stream->user = $user;
        $stream->init();
        $stream->activeQuery->andWhere(['>', 'content.created_at', $lastMailDate]);

        $activities = [];
        foreach ($stream->getWallEntries() as $wallEntry) {
            try {
                $activity = $wallEntry->content->getPolymorphicRelation();
                $activities[] = $activity->getActivityBaseClass();
            } catch (\yii\base\Exception $ex) {
                \Yii::error($ex->getMessage());
                return [];
            }
        }

        $user->updateAttributes([
            'last_activity_email' => new \yii\db\Expression('NOW()')
        ]);

        return $activities;
    }

}
