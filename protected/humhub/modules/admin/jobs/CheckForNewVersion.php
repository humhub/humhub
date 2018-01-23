<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\jobs;

use Yii;
use humhub\components\queue\ActiveJob;
use humhub\modules\user\models\Group;
use humhub\modules\admin\libs\HumHubAPI;
use humhub\modules\admin\notifications\NewVersionAvailable;

/**
 * CheckForNewVersion checks for new HumHub version and sends a notification to
 * the administrators
 *
 * @since 1.2
 * @author Luke
 */
class CheckForNewVersion extends ActiveJob
{

    /**
     * @inheritdoc
     */
    public function run()
    {
        $adminModule = Yii::$app->getModule('admin');

        if (!$adminModule->dailyCheckForNewVersion) {
            return;
        }
        if (!Yii::$app->params['humhub']['apiEnabled']) {
            return;
        }

        $latestVersion = HumHubAPI::getLatestHumHubVersion();

        if (!empty($latestVersion)) {

            $adminUsers = Group::getAdminGroup()->users;

            $latestNotifiedVersion = $adminModule->settings->get('lastVersionNotify');
            $adminsNotified = !($latestNotifiedVersion == "" || version_compare($latestVersion, $latestNotifiedVersion, ">"));
            $newVersionAvailable = (version_compare($latestVersion, Yii::$app->version, ">"));

            $updateNotification = new NewVersionAvailable();

            // Cleanup existing notifications
            if (!$newVersionAvailable || ($newVersionAvailable && !$adminsNotified)) {
                foreach ($adminUsers as $admin) {
                    $updateNotification->delete($admin);
                }
            }

            // Create new notification
            if ($newVersionAvailable && !$adminsNotified) {
                $updateNotification->sendBulk($adminUsers);
                $adminModule->settings->set('lastVersionNotify', $latestVersion);
            }
        }
    }

}
