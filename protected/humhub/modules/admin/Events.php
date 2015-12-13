<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin;

use Yii;
use humhub\modules\user\models\User;
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

        if (!Yii::$app->getModule('admin')->dailyCheckForNewVersion) {
            return;
        }
        if (!Yii::$app->params['humhub']['apiEnabled']) {
            return;
        }

        $controller->stdout("Checking for new HumHub version... ");

        $latestVersion = libs\HumHubAPI::getLatestHumHubVersion();
        if ($latestVersion != "") {
            $adminUserQuery = User::find()->where(['super_admin' => 1]);
            $latestNotifiedVersion = Setting::Get('lastVersionNotify', 'admin');
            $adminsNotified = !($latestNotifiedVersion == "" || version_compare($latestVersion, $latestNotifiedVersion, ">"));
            $newVersionAvailable = (version_compare($latestVersion, Yii::$app->version, ">"));
            $updateNotification = new notifications\NewVersionAvailable();

            // Cleanup existing notifications
            if (!$newVersionAvailable || ($newVersionAvailable && !$adminsNotified)) {
                foreach ($adminUserQuery->all() as $admin) {
                    $updateNotification->delete($admin);
                }
            }

            // Create new notification
            if ($newVersionAvailable && !$adminsNotified) {
                $updateNotification->sendBulk($adminUserQuery);
                Setting::Set('lastVersionNotify', $latestVersion, 'admin');
            }
        }

        $controller->stdout('done. ' . PHP_EOL, \yii\helpers\Console::FG_GREEN);
    }

    public static function onConsoleApplicationInit($event)
    {
        $application = $event->sender;
        $application->controllerMap['module'] = commands\ModuleController::className();
    }

}
