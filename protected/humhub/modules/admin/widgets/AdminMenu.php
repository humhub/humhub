<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use Yii;
use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\admin\permissions\ManageSpaces;
use humhub\modules\admin\permissions\SeeAdminInformation;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\LeftNavigation;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\admin\permissions\ManageGroups;

/**
 * AdminMenu
 *
 * @author luke
 */
class AdminMenu extends LeftNavigation
{

    /**
     * @inheritdoc
     */
    public $id = "admin-menu";

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->panelTitle = Yii::t('AdminModule.widgets_AdminMenuWidget', '<strong>Administration</strong> menu');

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Users'),
            'url' => ['/admin/user'],
            'icon' => 'user',
            'sortOrder' => 200,
            'isActive' => MenuLink::isActiveState('admin', ['user', 'group', 'approval', 'authentication', 'user-profile', 'pending-registrations']),
            'isVisible' => Yii::$app->user->can([
                ManageUsers::class,
                ManageSettings::class,
                ManageGroups::class
            ])
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Spaces'),
            'url' => ['/admin/space'],
            'icon' => 'inbox',
            'sortOrder' => 400,
            'isActive' => MenuLink::isActiveState('admin', 'space'),
            'isVisible' => Yii::$app->user->can([
                ManageSpaces::class,
                ManageSettings::class
            ])
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Modules'),
            'url' => ['/admin/module'],
            'icon' => 'rocket',
            'sortOrder' => 500,
            'isActive' => MenuLink::isActiveState('admin', 'module'),
            'isVisible' => Yii::$app->user->can(ManageModules::class)
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Settings'),
            'url' => ['/admin/setting'],
            'icon' => 'gears',
            'sortOrder' => 600,
            'isActive' => MenuLink::isActiveState('admin', 'setting'),
            'isVisible' => Yii::$app->user->can(ManageSettings::class)
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Information'),
            'url' => ['/admin/information'],
            'icon' => 'info-circle',
            'sortOrder' => 1000,
            'isActive' => MenuLink::isActiveState('admin', 'information'),
            'isVisible' => Yii::$app->user->can(SeeAdminInformation::class)
        ]));

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        // Workaround for modules with no admin menu permission support.
        if (!Yii::$app->user->isAdmin()) {
            foreach ($this->items as $key => $item) {
                if (!isset($item['isVisible'])) {
                    unset($this->items[$key]);
                }
            }
        }

        return parent::run();
    }


    public static function canAccess()
    {
        $canSeeAdminSection = Yii::$app->session->get('user.canSeeAdminSection');
        if ($canSeeAdminSection == null) {
            $canSeeAdminSection = Yii::$app->user->isAdmin() ? true : self::checkNonAdminAccess();
            Yii::$app->session->set('user.canSeeAdminSection', $canSeeAdminSection);
        }

        return $canSeeAdminSection;
    }

    private static function checkNonAdminAccess()
    {
        $adminMenu = new self();
        foreach ($adminMenu->items as $item) {
            if (isset($item['isVisible']) && $item['isVisible']) {
                return true;
            }
        }

        return false;
    }

}
