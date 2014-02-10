<?php

/**
 * @package humhub.modules.mail
 * @since 0.5
 */
class MailNotificationWidget extends HWidget {

    /**
     * Creates the Wall Widget
     */
    public function run() {

        $assetPrefix = Yii::app()->assetManager->publish(dirname(__FILE__) . '/../resources', true, 0, defined('YII_DEBUG'));
        Yii::app()->clientScript->registerCssFile($assetPrefix . '/mail.css');

        $this->render('mailNotifications', array(
        ));
    }

}

?>