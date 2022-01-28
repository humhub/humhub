<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\components\Application;
use humhub\modules\marketplace\Module;
use humhub\modules\ui\menu\MenuEntry;
use Yii;
use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\admin\permissions\ManageSpaces;
use humhub\modules\admin\permissions\SeeAdminInformation;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\LeftNavigation;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\admin\permissions\ManageGroups;
use yii\caching\DummyCache;

/**
 * AdminMenu implements the navigation in the administration section.
 *
 * Please note: Whenever there are entries visible for the current user, the "Administration" menu item
 * is displayed in the UserAccountMenu.
 *
 * The visibility of module menu entries should always be made based on the 'ManageModules' permission.
 * If a MenuEntry has no specified visibility, the permission `ManageModules` is automatically used.
 *
 * Example menu entry:
 *
 * ```php
 * $adminMenuWidget->addEntry(new MenuLink([
 *     'id' => 'modules',
 *     'label' => Yii::t('ExampleModule.base', 'Your cool module'),
 *     'url' => ['/example/module/admin'],
 *     'icon' => 'rocket',
 *     'sortOrder' => 500,
 *     'isActive' => MenuLink::isActiveState('example', 'module'),
 *     'isVisible' => Yii::$app->user->can(ManageModules::class)
 *  ]));
 * ```
 *
 * @author luke
 */
class AdminMenu extends LeftNavigation
{
    const SESSION_CAN_SEE_ADMIN_SECTION = 'user.canSeeAdminSection';

    /**
     * @inheritdoc
     */
    public $id = "admin-menu";

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->panelTitle = Yii::t('AdminModule.base', '<strong>Administration</strong> menu');

        $this->addEntry(new MenuLink([
            'id' => 'user',
            'label' => Yii::t('AdminModule.base', 'Users'),
            'url' => ['/admin/user'],
            'icon' => 'user',
            'sortOrder' => 200,
            'isActive' => MenuLink::isActiveState('admin', ['user', 'group', 'approval', 'authentication', 'user-profile', 'pending-registrations', 'user-permissions', 'user-people']) ||
                MenuLink::isActiveState('ldap', 'admin'),
            'isVisible' => Yii::$app->user->can([
                ManageUsers::class,
                ManageSettings::class,
                ManageGroups::class
            ])
        ]));

        $this->addEntry(new MenuLink([
            'id' => 'spaces',
            'label' => Yii::t('AdminModule.base', 'Spaces'),
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
            'id' => 'modules',
            'label' => Yii::t('AdminModule.base', 'Modules') . $this->getMarketplaceUpdatesBadge(),
            'url' => ['/admin/module'],
            'icon' => 'rocket',
            'sortOrder' => 500,
            'htmlOptions' => ['class' => 'modules'],
            'isActive' => MenuLink::isActiveState('admin', 'module'),
            'isVisible' => Yii::$app->user->can(ManageModules::class)
        ]));

        $this->addEntry(new MenuLink([
            'id' => 'settings',
            'label' => Yii::t('AdminModule.base', 'Settings'),
            'url' => ['/admin/setting'],
            'icon' => 'gears',
            'sortOrder' => 600,
            'isActive' => MenuLink::isActiveState('admin', 'setting'),
            'isVisible' => Yii::$app->user->can(ManageSettings::class)
        ]));

        $this->addEntry(new MenuLink([
            'id' => 'information',
            'label' => Yii::t('AdminModule.base', 'Information'),
            'url' => ['/admin/information'],
            'icon' => 'info-circle',
            'sortOrder' => 1000,
            'isActive' => MenuLink::isActiveState('admin', 'information'),
            'isVisible' => Yii::$app->user->can(SeeAdminInformation::class)
        ]));

        parent::init();
    }

    /**
     * Returns whether the current user can see the admin menu.
     *
     * @return bool
     */
    public static function canAccess()
    {
        if (!(Yii::$app instanceof Application)) {
            return false;
        }

        $canSeeAdminSection = Yii::$app->session->get(static::SESSION_CAN_SEE_ADMIN_SECTION);
        if ($canSeeAdminSection == null) {
            $canSeeAdminSection = Yii::$app->user->isAdmin() ?
                true :
                !empty((new self())->getEntries(null, true));
            Yii::$app->session->set(static::SESSION_CAN_SEE_ADMIN_SECTION, $canSeeAdminSection);
        }

        return (bool)$canSeeAdminSection;
    }

    /**
     * Resets the caching, if the current user can see the AdminMenu.
     */
    public static function reset()
    {
        if (Yii::$app instanceof Application) {
            Yii::$app->session->remove(static::SESSION_CAN_SEE_ADMIN_SECTION);
        }
    }

    /**
     * @inheritDoc
     * @notice If the MenuEntry has not specified visibility, the Permission ManageModules is automatically used.
     */
    public function addEntry(MenuEntry $entry)
    {
        if (!$entry->isVisibilitySet()) {
            $entry->setIsVisible(Yii::$app->user->can(ManageModules::class));
        }

        parent::addEntry($entry);
    }


    /**
     * @return string
     */
    private function getMarketplaceUpdatesBadge()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('marketplace');

        if (!$module->enabled) {
            return '';
        }

        if (Yii::$app->cache instanceof DummyCache) {
            return '';
        }

        try {
            $updatesCount = count($module->onlineModuleManager->getModuleUpdates());
            if ($updatesCount > 0) {
                return '&nbsp;&nbsp;<span class="label label-danger">' . $updatesCount . '</span>';
            }
        } catch (\Exception $ex) {
            ;
        }

        return '';
    }

}
