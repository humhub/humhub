<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\components\Application;
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
    const SESSION_CAN_SEE_ADMIN_SECTION = 'user.canSeeAdminSection';

    public $type = "adminNavigation";
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
            'isActive' => MenuLink::isActiveState('admin', ['user', 'group', 'approval', 'authentication', 'user-profile', 'pending-registrations']),
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
            'label' => Yii::t('AdminModule.base', 'Modules'),
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
     * @inheritdoc
     */
    public function addItem($entryArray)
    {
        $entry = MenuLink::createByArray($entryArray);

        if(!isset($entryArray['isVisible'])) {
            $entry->setIsVisible(Yii::$app->user->isAdmin());
        }

        $this->addEntry($entry);
    }

    public static function canAccess()
    {
        if(!(Yii::$app instanceof Application)) {
            return false;
        }

        $canSeeAdminSection = Yii::$app->session->get(static::SESSION_CAN_SEE_ADMIN_SECTION);
        if ($canSeeAdminSection == null) {
            $canSeeAdminSection = Yii::$app->user->isAdmin() ? true : self::checkNonAdminAccess();
            Yii::$app->session->set(static::SESSION_CAN_SEE_ADMIN_SECTION, $canSeeAdminSection);
        }

        return $canSeeAdminSection;
    }

    public static function reset()
    {
        if(Yii::$app instanceof Application) {
            Yii::$app->session->remove(static::SESSION_CAN_SEE_ADMIN_SECTION);
        }
    }

    private static function checkNonAdminAccess()
    {
        return Yii::$app->user->can([ManageGroups::class, ManageModules::class, ManageSettings::class, ManageUsers::class, SeeAdminInformation::class]);
    }

}
