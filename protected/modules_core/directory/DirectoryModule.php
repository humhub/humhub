<?php

/**
 * Directory Base Module
 *
 * The directory module adds a menu item "Directory" to the top navigation
 * with some lists about spaces, users or group inside the application.
 *
 * @package humhub.modules_core.directory
 * @since 0.5
 */
class DirectoryModule extends CWebModule {

    /**
     * Inits the Module
     */
    public function init() {

        $this->setImport(array(
        ));
    }

    /**
     * On build of the TopMenu, check if module is enabled
     * When enabled add a menu item
     *
     * @param type $event
     */
    public static function onTopMenuInit($event) {

        // Is Module enabled on this workspace?
        if (Yii::app()->moduleManager->isEnabled('directory')) {
            $event->sender->addItem(array(
                'label' => Yii::t('DirectoryModule.base', 'Directory'),
                'url' => Yii::app()->createUrl('//directory/directory'),
                'sortOrder' => 400,
                'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'directory'),
            ));
        }
    }

}