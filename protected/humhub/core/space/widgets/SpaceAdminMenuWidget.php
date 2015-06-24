<?php

/**
 * The Admin Navigation for spaces
 *
 * @author Luke
 * @package humhub.modules_core.space.widgets
 * @since 0.5
 */
class SpaceAdminMenuWidget extends MenuWidget
{

    public $space;
    public $template = "application.widgets.views.leftNavigation";

    public function init()
    {

        /**
         * Backward compatibility - try to auto load space based on current
         * controller.
         */
        if ($this->space === null) {
            $this->space = Yii::app()->getController()->getSpace();
        }


        $this->addItemGroup(array(
            'id' => 'admin',
            'label' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', '<strong>Space</strong> preferences'),
            'sortOrder' => 100,
        ));

        // check user rights
        if ($this->space->isAdmin()) {
            $this->addItem(array(
                'label' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', 'General'),
                'group' => 'admin',
                'url' => $this->space->createUrl('//space/admin/edit'),
                'icon' => '<i class="fa fa-cogs"></i>',
                'sortOrder' => 100,
                'isActive' => (Yii::app()->controller->id == "admin" && Yii::app()->controller->action->id == "edit"),
            ));
        }

        // check user rights
        if ($this->space->isAdmin()) {
            $this->addItem(array(
                'label' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', 'Members'),
                'group' => 'admin',
                'url' => $this->space->createUrl('//space/admin/members'),
                'icon' => '<i class="fa fa-group"></i>',
                'sortOrder' => 200,
                'isActive' => (Yii::app()->controller->id == "admin" && Yii::app()->controller->action->id == "members"),
            ));
        }

        // check user rights
        if ($this->space->isAdmin()) {
            $this->addItem(array(
                'label' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', 'Modules'),
                'group' => 'admin',
                'url' => $this->space->createUrl('//space/admin/modules'),
                'icon' => '<i class="fa fa-rocket"></i>',
                'sortOrder' => 300,
                'isActive' => (Yii::app()->controller->id == "admin" && Yii::app()->controller->action->id == "modules"),
            ));
        }

        parent::init();
    }

}

?>
