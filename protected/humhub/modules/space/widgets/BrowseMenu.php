<?php

namespace humhub\modules\space\widgets;

use Yii;

/**
 * The Main Navigation for a space. It includes the Modules the Stream
 *
 * @author Luke
 * @package humhub.modules_core.space.widgets
 * @since 0.5
 */
class BrowseMenu extends MenuWidget
{

    public $template = 'application.widgets.views.leftNavigation';

    public function init()
    {

        $this->addItemGroup([
            'id' => 'browse',
            'label' => Yii::t('SpaceModule.widgets_SpaceBrowseMenuWidget', 'Spaces'),
            'sortOrder' => 100,
        ]);

        $this->addItem([
            'label' => Yii::t('SpaceModule.widgets_SpaceBrowseMenuWidget', 'My Space List'),
            'url' => Yii::app()->createUrl('/space/browse', []),
            'sortOrder' => 100,
            'isActive' => (Yii::app()->controller->id == "spacebrowse" && Yii::app()->controller->action->id === 'index'),
        ]);

        $this->addItem([
            'label' => Yii::t('SpaceModule.widgets_SpaceBrowseMenuWidget', 'My space summary'),
            'url' => Yii::app()->createUrl('/dashboard', []),
            'sortOrder' => 100,
            'isActive' => (Yii::app()->controller->id == "spacebrowse" && Yii::app()->controller->action->id === 'index'),
        ]);

        $this->addItem([
            'label' => Yii::t('SpaceModule.widgets_SpaceBrowseMenuWidget', 'Space directory'),
            'url' => Yii::app()->createUrl('/community/workspaces', []),
            'sortOrder' => 200,
            'isActive' => (Yii::app()->controller->id == "spacebrowse" && Yii::app()->controller->action->id === 'index'),
        ]);

        parent::init();
    }

}
