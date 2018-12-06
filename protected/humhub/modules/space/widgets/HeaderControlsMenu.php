<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\widgets;

use humhub\modules\space\models\Space;
use humhub\modules\ui\menu\widgets\DropDownMenu;
use Yii;

/**
 * The Admin Navigation for spaces
 *
 * @author Luke
 * @package humhub.modules_core.space.widgets
 * @since 0.5
 */
class HeaderControlsMenu extends DropDownMenu
{
    /**
     * @var Space
     */
    public $space;

    /**
     * @inheritdoc
     */
    public $label = '<i class="fa fa-cog"></i>';

    /**
     * @inheritdoc
     */
    public function init()
    {

        if ($this->template === '@humhub/widgets/views/dropdownNavigation') {
            $this->template = '@ui/menu/widgets/views/dropdown-menu.php';
        }


        // check user rights
        if ($this->space->isAdmin()) {

            $this->addItem([
                'label' => Yii::t('SpaceModule.base', 'Settings'),
                'url' => $this->space->createUrl('/space/manage'),
                'icon' => '<i class="fa fa-cogs"></i>',
                'sortOrder' => 100,
                'isActive' => (Yii::$app->controller->id === 'default'),
            ]);

            $this->addItem([
                'label' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', 'Security'),
                'url' => $this->space->createUrl('/space/manage/security'),
                'icon' => '<i class="fa fa-lock"></i>',
                'sortOrder' => 200,
                'isActive' => (Yii::$app->controller->id === 'security'),
            ]);

            $this->addItem([
                'label' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', 'Members'),
                'url' => $this->space->createUrl('/space/manage/member'),
                'icon' => '<i class="fa fa-group"></i>',
                'sortOrder' => 200,
                'isActive' => (Yii::$app->controller->id === 'member'),
            ]);

            $this->addItem([
                'label' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', 'Modules'),
                'url' => $this->space->createUrl('/space/manage/module'),
                'icon' => '<i class="fa fa-rocket"></i>',
                'sortOrder' => 300,
                'isActive' => (Yii::$app->controller->id === 'module'),
            ]);
        }

        if ($this->space->isMember()) {

            $membership = $this->space->getMembership();

            if (!$membership->send_notifications) {
                $this->addItem([
                    'label' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', 'Receive Notifications for new content'),
                    'url' => $this->space->createUrl('/space/membership/receive-notifications'),
                    'icon' => '<i class="fa fa-bell"></i>',
                    'sortOrder' => 300,
                    'isActive' => (Yii::$app->controller->id === 'module'),
                    'htmlOptions' => ['data-method' => 'POST']
                ]);
            } else {
                $this->addItem([
                    'label' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', 'Don\'t receive notifications for new content'),
                    'url' => $this->space->createUrl('/space/membership/revoke-notifications'),
                    'icon' => '<i class="fa fa-bell-o"></i>',
                    'sortOrder' => 300,
                    'isActive' => (Yii::$app->controller->id === 'module'),
                    'htmlOptions' => ['data-method' => 'POST']
                ]);
            }

            if (!$this->space->isSpaceOwner() && $this->space->canLeave()) {
                $this->addItem([
                    'label' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', 'Cancel Membership'),
                    'url' => $this->space->createUrl('/space/membership/revoke-membership'),
                    'icon' => '<i class="fa fa-times"></i>',
                    'sortOrder' => 300,
                    'isActive' => (Yii::$app->controller->id === 'module'),
                    'htmlOptions' => ['data-method' => 'POST']
                ]);
            }

            if ($membership->show_at_dashboard) {

                $this->addItem([
                    'label' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', 'Hide posts on dashboard'),
                    'url' => $this->space->createUrl('/space/membership/switch-dashboard-display', ['show' => 0]),
                    'icon' => '<i class="fa fa-eye-slash"></i>',
                    'sortOrder' => 400,
                    'isActive' => (Yii::$app->controller->id === 'module'),
                    'htmlOptions' => [
                        'data-method' => 'POST',
                        'class' => 'tt',
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'left',
                        'title' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', 'This option will hide new content from this space at your dashboard')
                    ]
                ]);
            } else {
                $this->addItem([
                    'label' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', 'Show posts on dashboard'),
                    'url' => $this->space->createUrl('/space/membership/switch-dashboard-display', ['show' => 1]),
                    'icon' => '<i class="fa fa-eye"></i>',
                    'sortOrder' => 400,
                    'isActive' => (Yii::$app->controller->id === 'module'),
                    'htmlOptions' => ['data-method' => 'POST',
                        'class' => 'tt',
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'left',
                        'title' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', 'This option will show new content from this space at your dashboard')
                    ]
                ]);
            }
        }

        return parent::init();
    }

}
