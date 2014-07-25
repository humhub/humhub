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
class DirectoryModule extends HWebModule
{

    public $isCoreModule = true;

    /**
     * Inits the Module
     */
    public function init()
    {

        $this->setImport(array(
        ));
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
            'label' => Yii::t('DirectoryModule.base', 'Directory'),
            'id' => 'directory',
            'icon' => '<i class="fa fa-book"></i>',
            'url' => Yii::app()->createUrl('//directory/directory'),
            'sortOrder' => 400,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'directory'),
        ));
    }

}
