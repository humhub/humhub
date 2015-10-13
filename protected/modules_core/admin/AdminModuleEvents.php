<?php

/**
 * @package humhub.modules_core.admin
 * @since 0.5
 */
class AdminModuleEvents
{

    /**
     * On Init of Dashboard Sidebar, add the approve notification widget
     *
     * @param type $event
     */
    public static function onDashboardSidebarInit($event)
    {
        if (Yii::app()->user->canApproveUsers()) {
            $event->sender->addWidget('application.modules_core.admin.widgets.ApprovalDashboardWidget', array(), array('sortOrder' => 99));
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
            foreach (User::model()->findAllByAttributes(array('super_admin' => 1)) as $user) {
                $notification = Notification::model()->findByAttributes(array('class' => 'HumHubUpdateNotification', 'user_id' => $user->id));
                if ($notification === null) {
                    $notification = new Notification();
                    $notification->class = "HumHubUpdateNotification";
                    $notification->user_id = $user->id;
                    $notification->save();
                }
            }
        }
    }

}
