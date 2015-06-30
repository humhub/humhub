<?php

namespace humhub\core\admin\notifications;

use humhub\core\notification\components\BaseNotification;
use Yii;

/**
 * HumHubUpdateNotification
 * 
 * Notifies about new HumHub Version
 *
 * @package humhub.modules_core.admin.notifications
 * @since 0.11
 */
class NewVersionAvailable extends BaseNotification
{

    public $viewName = 'newVersionAvailable';

    public function renderText()
    {
        return Yii::t('AdminModule.views_notifications_newUpdate', "There is a new HumHub Version (%version%) available.", ['%version%' => $notification->getLatestHumHubVersion()]);
    }

    public function getUrl()
    {
        return \yii\helpers\Url::to(['/admin/about']);
    }

    public function getLatestHumHubVersion()
    {
        $onlineModuleManager = new \humhub\core\admin\libs\OnlineModuleManager();
        return $onlineModuleManager->getLatestHumHubVersion();
    }

}

?>
