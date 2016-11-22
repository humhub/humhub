<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin;

use Yii;
use humhub\modules\admin\models\Log;


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

        if (Yii::$app->getModule('user')->settings->get('auth.needApproval')) {
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

        $controller->stdout("Deleting old logs... ");
        
        $settings = Yii::$app->settings;
        $timeAgo = strtotime($settings->get('logsDateLimit'));
        $deleted = Log::deleteAll(['<', 'log_time', $timeAgo]);

        $controller->stdout('done - ' . $deleted . ' records deleted.' . PHP_EOL, \yii\helpers\Console::FG_GREEN);


        if (!Yii::$app->getModule('admin')->dailyCheckForNewVersion) {
            return;
        }
        if (!Yii::$app->params['humhub']['apiEnabled']) {
            return;
        }

        $controller->stdout("Checking for new HumHub version... ");

        $latestVersion = libs\HumHubAPI::getLatestHumHubVersion();
        if ($latestVersion != "") {
            $adminUsers = \humhub\modules\user\models\Group::getAdminGroup()->users;
            $latestNotifiedVersion = Yii::$app->getModule('admin')->settings->get('lastVersionNotify');
            $adminsNotified = !($latestNotifiedVersion == "" || version_compare($latestVersion, $latestNotifiedVersion, ">"));
            $newVersionAvailable = (version_compare($latestVersion, Yii::$app->version, ">"));
            $updateNotification = new notifications\NewVersionAvailable();

            // Cleanup existing notifications
            if (!$newVersionAvailable || ($newVersionAvailable && !$adminsNotified)) {
                foreach ($adminUsers as $admin) {
                    $updateNotification->delete($admin);
                }
            }

            // Create new notification
            if ($newVersionAvailable && !$adminsNotified) {
                $updateNotification->sendBulk($adminUsers);
                Yii::$app->getModule('admin')->settings->set('lastVersionNotify', $latestVersion);
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
