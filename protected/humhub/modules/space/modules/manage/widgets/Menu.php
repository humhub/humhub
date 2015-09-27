<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\widgets;

use Yii;
use \yii\base\Widget;

/**
 * The Admin Navigation for spaces
 *
 * @author Luke
 * @package humhub.modules_core.space.widgets
 * @since 0.5
 */
class Menu extends \humhub\widgets\BaseMenu
{

    public $space;
    public $template = "@humhub/widgets/views/leftNavigation";

    public function init()
    {
        // check user rights
        if (!$this->space->isAdmin()) {
            return parent::init();
        }

        $this->addItemGroup(array(
            'id' => 'admin',
            'label' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', '<strong>Space</strong> preferences'),
            'sortOrder' => 100,
        ));

        $this->addItem(array(
            'label' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', 'General'),
            'group' => 'admin',
            'url' => $this->space->createUrl('/space/manage'),
            'icon' => '<i class="fa fa-cogs"></i>',
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->id == "default"),
        ));

        $this->addItem(array(
            'label' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', 'Members'),
            'group' => 'admin',
            'url' => $this->space->createUrl('/space/manage/member'),
            'icon' => '<i class="fa fa-group"></i>',
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->id == "member"),
        ));

        $this->addItem(array(
            'label' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', 'Modules'),
            'group' => 'admin',
            'url' => $this->space->createUrl('/space/manage/module'),
            'icon' => '<i class="fa fa-rocket"></i>',
            'sortOrder' => 300,
            'isActive' => (Yii::$app->controller->id == "module"),
        ));

        return parent::init();
    }

}

?>
