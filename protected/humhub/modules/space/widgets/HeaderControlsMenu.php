<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\widgets\BaseMenu;
use Yii;

/**
 * The Admin Navigation for spaces
 *
 * @author Luke
 * @package humhub.modules_core.space.widgets
 * @since 0.5
 */
class HeaderControlsMenu extends BaseMenu
{

    public $user;
    public $template = '@humhub/widgets/views/leftNavigation';

    public function init()
    {

        $this->addItemGroup([
            'id' => 'admin',
            'label' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', '<i class="fa fa-cog"></i>'),
            'sortOrder' => 100,
        ]);

        // check user rights
        if ($this->space->isAdmin()) {

            $this->addItem([
                'label' => Yii::t('SpaceModule.base', 'Edit Profile'),
                'group' => 'admin',
                'url' => $this->space->createUrl('/user/account/edit'),
                'icon' => '<i class="fa fa-cogs"></i>',
                'sortOrder' => 100,
                'isActive' => (Yii::$app->controller->id === 'default'),
            ]);

            $this->addItem([
                'label' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', 'Privacy'),
                'group' => 'admin',
                'url' => $this->space->createUrl('/user/account/security'),
                'icon' => '<i class="fa fa-lock"></i>',
                'sortOrder' => 200,
                'isActive' => (Yii::$app->controller->id === 'security'),
            ]);

            $this->addItem([
                'label' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', 'Friends'),
                'group' => 'admin',
                'url' => $this->space->createUrl('/friendship/manage'),
                'icon' => '<i class="fa fa-group"></i>',
                'sortOrder' => 200,
                'isActive' => (Yii::$app->controller->id === 'member'),
            ]);

            $this->addItem([
                'label' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', 'Modules'),
                'group' => 'admin',
                'url' => $this->space->createUrl('/user/account/edit-modules'),
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
                    'group' => 'admin',
                    'url' => $this->space->createUrl('/space/membership/receive-notifications'),
                    'icon' => '<i class="fa fa-bell"></i>',
                    'sortOrder' => 300,
                    'isActive' => (Yii::$app->controller->id === 'module'),
                    'htmlOptions' => ['data-method' => 'POST']
                ]);
            } else {
                $this->addItem([
                    'label' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', 'Don\'t receive notifications for new content'),
                    'group' => 'admin',
                    'url' => $this->space->createUrl('/space/membership/revoke-notifications'),
                    'icon' => '<i class="fa fa-bell-o"></i>',
                    'sortOrder' => 300,
                    'isActive' => (Yii::$app->controller->id === 'module'),
                    'htmlOptions' => ['data-method' => 'POST']
                ]);
            }

            if ($membership->show_at_dashboard) {

                $this->addItem([
                    'label' => Yii::t('SpaceModule.widgets_SpaceAdminMenuWidget', 'Hide posts on dashboard'),
                    'group' => 'admin',
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
                    'group' => 'admin',
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
