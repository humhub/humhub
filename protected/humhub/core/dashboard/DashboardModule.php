<?php

/**
 * Dashboard Base Module
 *
 * @package humhub.modules_core.dashboard
 * @since 0.5
 */
class DashboardModule extends HWebModule
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

        // Is Module enabled on this workspace?
        $event->sender->addItem(array(
            'label' => Yii::t('DashboardModule.base', 'Dashboard'),
            'id' => 'dashboard',
            'icon' => '<i class="fa fa-tachometer"></i>',
            'url' => Yii::app()->createUrl('//dashboard/dashboard'),
            'sortOrder' => 100,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'dashboard'),
        ));
    }

}
