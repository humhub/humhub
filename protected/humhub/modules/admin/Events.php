<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin;

use Yii;
use humhub\modules\admin\libs\OnlineModuleManager;
use humhub\models\Setting;

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
        if (Yii::$app->user->isGuest) {
            return;
        }

        if (Setting::Get('needApproval', 'authentication_internal')) {
            if (Yii::$app->user->getIdentity()->canApproveUsers()) {
                $event->sender->addWidget(widgets\DashboardApproval::className(), array(), array('sortOrder' => 99));
            }
        }
    }

    /**
     * Check if there is a new Humhub Version available and sends a notification
     * to super admins
     *
     * @param \yii\base\Event $event
     */
    public static function onCronDailyRun($event)
    {
        $controller = $event->sender;

        if (!Yii::$app->getModule('admin')->marketplaceEnabled) {
            return;
        }

        $controller->stdout("Checking for new HumHub version... ");

        $onlineModuleManager = new OnlineModuleManager();
        $latestVersion = $onlineModuleManager->getLatestHumHubVersion();

        if ($latestVersion != "" && version_compare($latestVersion, Yii::$app->version, ">")) {
            $notification = new notifications\NewVersionAvailable();
            $notification->add(User::find()->where(['super_admin' => 1]));
        }

        $controller->stdout('done. ' . PHP_EOL, \yii\helpers\Console::FG_GREEN);
    }

    public static function onConsoleApplicationInit($event)
    {
        $application = $event->sender;
        $application->controllerMap['module'] = commands\ModuleController::className();
    }

}
