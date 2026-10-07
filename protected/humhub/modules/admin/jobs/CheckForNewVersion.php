<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\jobs;

use humhub\modules\admin\libs\HumHubAPI;
use humhub\modules\admin\Module;
use humhub\modules\admin\notifications\NewVersionAvailableNotification;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\queue\ActiveJob;
use humhub\modules\user\models\Group;
use Yii;

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
        /** @var Module $adminModule */
        $adminModule = Yii::$app->getModule('admin');

        if (!$adminModule->dailyCheckForNewVersion || !Yii::$app->params['humhub']['apiEnabled']) {
            return;
        }

        $latestVersion = HumHubAPI::getLatestHumHubVersion();

        if (!empty($latestVersion)) {

            $adminUserQuery = Group::getAdminGroup()->getUsers();

            $latestNotifiedVersion = $adminModule->settings->get('lastVersionNotify');
            $adminsNotified = !($latestNotifiedVersion == "" || version_compare($latestVersion, $latestNotifiedVersion, ">"));
            $newVersionAvailable = (version_compare($latestVersion, Yii::$app->version, ">"));

            // Cleanup existing notifications - only administrators receive them, so all rows of the class
            if (!$newVersionAvailable || ($newVersionAvailable && !$adminsNotified)) {
                NotificationManager::delete(NewVersionAvailableNotification::class);
            }

            // Create new notification
            if ($newVersionAvailable && !$adminsNotified) {
                NotificationManager::dispatch(NewVersionAvailableNotification::class, $adminUserQuery, options: [
                    'payload' => ['version' => $latestVersion],
                ]);
                $adminModule->settings->set('lastVersionNotify', $latestVersion);
            }
        }
    }

}
