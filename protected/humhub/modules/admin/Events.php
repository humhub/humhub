<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin;

use humhub\components\Application;
use humhub\modules\admin\widgets\AdminMenu;
use humhub\modules\user\events\UserEvent;
use Yii;

/**
 * Admin Module provides the administrative backend for HumHub installations.
 *
 * @since 0.5
 */
class Events extends \yii\base\BaseObject
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
                $event->sender->addWidget(widgets\DashboardApproval::class, [], [
                    'sortOrder' => 99
                ]);
            }
        }

        $event->sender->addWidget(widgets\IncompleteSetupWarning::class, [], ['sortOrder' => 1]);
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
     * @param $event UserEvent
     */
    public static function onSwitchUser($event) {
        if(Yii::$app instanceof Application) {
            AdminMenu::reset();
        }
    }
}
