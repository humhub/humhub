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

        $this->addItemGroup(array(
            'id' => 'admin',
            'label' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', '<i class="fa fa-cog"></i>'),
            'sortOrder' => 100,
        ));

        // check user rights
        if ($this->space->isAdmin()) {

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
        }

        if (!$this->space->isSpaceOwner() && $this->space->isMember()) {
            $this->addItem(array(
                'label' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', 'Cancel Membership'),
                'group' => 'admin',
                'url' => $this->space->createUrl('/space/membership/revoke-membership'),
                'icon' => '<i class="fa fa-times"></i>',
                'sortOrder' => 300,
                'isActive' => (Yii::$app->controller->id == "module"),
                'htmlOptions' => ['data-method' => 'POST']

            ));
        }


        if (!Yii::$app->user->isGuest || $this->space->isMember()) {

            $membership = $this->space->getMembership();

            if ($membership !== null) {
                if ($membership->show_at_dashboard) {

                    $this->addItem(array(
                        'label' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', 'Hide posts on dashboard'),
                        'group' => 'admin',
                        'url' => $this->space->createUrl('/space/membership/switch-dashboard-display', ['show' => 0]),
                        'icon' => '<i class="fa fa-eye-slash"></i>',
                        'sortOrder' => 400,
                        'isActive' => (Yii::$app->controller->id == "module"),
                        'htmlOptions' => [
                            'data-method' => 'POST',
                            'class' => 'tt',
                            'data-toggle' => 'tooltip',
                            'data-placement' => 'left',
                            'title' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', 'This option will hide new content from this space at your dashboard')
                        ]
                    ));

                } else {

                    $this->addItem(array(
                        'label' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', 'Show posts on dashboard'),
                        'group' => 'admin',
                        'url' => $this->space->createUrl('/space/membership/switch-dashboard-display', ['show' => 1]),
                        'icon' => '<i class="fa fa-eye"></i>',
                        'sortOrder' => 400,
                        'isActive' => (Yii::$app->controller->id == "module"),
                        'htmlOptions' => ['data-method' => 'POST',
                            'class' => 'tt',
                            'data-toggle' => 'tooltip',
                            'data-placement' => 'left',
                            'title' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', 'This option will show new content from this space at your dashboard')
                        ]
                    ));

                }

            }
        }


        return parent::init();
    }

}

?>
