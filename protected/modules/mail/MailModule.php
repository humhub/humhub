<?php

/**
 * MailModule provides messaging functions inside the application.
 *
 * @package humhub.modules.mail
 * @since 0.5
 */
class MailModule extends HWebModule
{

    public function init()
    {

        $this->setImport(array(
            'mail.models.*',
            'mail.controllers.*',
            'mail.behaviors.*',
            'mail.forms.*',
        ));
    }

    /**
     * On User delete, also delete all comments
     *
     * @param type $event
     */
    public static function onUserDelete($event)
    {

        Yii::import('application.modules.mail.models.*');

        // Delete all message entries
        foreach (MessageEntry::model()->findAllByAttributes(array('user_id' => $event->sender->id)) as $messageEntry) {
            $messageEntry->delete();
        }

        // Leaves all my conversations
        foreach (UserMessage::model()->findAllByAttributes(array('created_by' => $event->sender->id)) as $userMessage) {
            $userMessage->leave();
        }

        return true;
    }

    /**
     * On run of integrity check command, validate all module data
     *
     * @param type $event
     */
    public static function onIntegrityCheck($event)
    {

        $integrityChecker = $event->sender;
        #$integrityChecker->showTestHeadline("Validating Mail Module (" . Message::model()->count() . " entries)");
    }

    /**
     * On build of the TopMenu, check if module is enabled
     * When enabled add a menu item
     *
     * @param type $event
     */
    public static function onTopMenuInit($event)
    {

        $event->sender->addItem(array(
            'label' => Yii::t('MailModule.base', 'Messages'),
            'url' => Yii::app()->createUrl('//mail/mail/index', array()),
            'icon' => 'mail',
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'mail'),
            'sortOrder' => 300,
        ));
    }

    public static function onNotificationAddonInit($event)
    {
        $event->sender->addWidget('application.modules.mail.widgets.MailNotificationWidget', array(), array('sortOrder' => 90));
    }

}
