<?php

namespace humhub\core\admin;

use Yii;

/**
 * @package humhub.modules_core.admin
 * @since 0.5
 */
class Events extends \yii\base\Object
{

    /**
     * On Init of Dashboard Sidebar, add the approve notification widget
     *
     * @param type $event
     */
    public static function onDashboardSidebarInit($event)
    {
        if (Yii::$app->user->getIdentity()->canApproveUsers()) {
            $event->sender->addWidget(widgets\DashboardApproval::className(), array(), array('sortOrder' => 99));
        }
    }

    /**
     * Check if there is a new Humhub Version available
     *
     * @param type $event
     */
    public static function onCronDailyRun($event)
    {
        Yii::import('application.modules_core.admin.libs.*');
        $cron = $event->sender;

        if (!Yii::app()->getModule('admin')->marketplaceEnabled) {
            return;
        }

        $onlineModuleManager = new OnlineModuleManager();
        $latestVersion = $onlineModuleManager->getLatestHumHubVersion();

        if ($latestVersion != "" && version_compare($latestVersion, HVersion::VERSION, ">")) {
            $notification = new notifications\NewVersionAvailable;
            $notification->add(User::find()->where(['super_admin' => 1]));
        }
    }

}
