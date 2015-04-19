<?php

/**
 * HumHubUpdateNotification
 * 
 * Notifies about new HumHub Version
 *
 * @package humhub.modules_core.admin.notifications
 * @since 0.11
 */
class HumHubUpdateNotification extends Notification
{

    public $webView = "admin.views.notifications.newUpdate";
    public $mailView = "application.modules_core.admin.views.notifications.newUpdate_mail";

    public function redirectToTarget()
    {
        Yii::app()->getController()->redirect(Yii::app()->getController()->createUrl('//admin/about'));
    }

    public function getLatestHumHubVersion()
    {
        Yii::import('application.modules_core.admin.libs.*');        
        $onlineModuleManager = new OnlineModuleManager();
        return $onlineModuleManager->getLatestHumHubVersion();
    }

}

?>
