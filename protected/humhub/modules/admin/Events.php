<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin;

use Yii;

/**
 * Admin Module provides the administrative backend for HumHub installations.
 *
 * @since 0.5
 */
class Events extends \yii\base\Object
{

    /**
     * On Init of Dashboard Sidebar, add the approve notification widget
     *
     * @param \yii\base\Event $event the event
     */
    public static function onDashboardSidebarInit($event)
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        if (Yii::$app->getModule('user')->settings->get('auth.needApproval')) {
            if (Yii::$app->user->getIdentity()->canApproveUsers()) {
                $event->sender->addWidget(widgets\DashboardApproval::className(), [], [
                    'sortOrder' => 99
                ]);
            }
        }
    }

    /**
     * Callback on daily cron job run
     *
     * @param \yii\base\Event $event
     */
    public static function onCronDailyRun($event)
    {
        Yii::$app->queue->push(new jobs\CleanupLog());
        Yii::$app->queue->push(new jobs\CheckForNewVersion());
    }

    /**
     * On console application initialization
     *
     * @param \yii\base\Event $event
     */
    public static function onConsoleApplicationInit($event)
    {
        $application = $event->sender;
        $application->controllerMap['module'] = commands\ModuleController::className();
    }

}
