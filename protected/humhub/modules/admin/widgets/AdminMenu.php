<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\modules\ui\menu\MenuEntry;
use humhub\modules\ui\menu\widgets\LeftNavigation;
use humhub\modules\ui\widgets\Icon;
use Yii;

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

        $entry = new MenuEntry();
        $entry->setLabel(Yii::t('AdminModule.widgets_AdminMenuWidget', 'Users'));
        $entry->setUrl(['/admin/user']);
        $entry->setIcon(new Icon(['name' => 'user']));
        $entry->setSortOrder(200);
        $entry->setIsActive((Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && (Yii::$app->controller->id == 'user' || Yii::$app->controller->id == 'group' || Yii::$app->controller->id == 'approval' || Yii::$app->controller->id == 'authentication' || Yii::$app->controller->id == 'user-profile' || Yii::$app->controller->id == 'pending-registrations')));
        $entry->setIsVisible(Yii::$app->user->can([
            new \humhub\modules\admin\permissions\ManageUsers(),
            new \humhub\modules\admin\permissions\ManageSettings(),
            new \humhub\modules\admin\permissions\ManageGroups()
        ]));
        $this->addEntry($entry);

        $entry = new MenuEntry();
        $entry->setLabel(Yii::t('AdminModule.widgets_AdminMenuWidget', 'Spaces'));
        $entry->setUrl(['/admin/space']);
        $entry->setIcon(new Icon(['name' => 'inbox']));
        $entry->setSortOrder(400);
        $entry->setIsActive((Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'space'));
        $entry->setIsVisible(Yii::$app->user->can([
            new \humhub\modules\admin\permissions\ManageSpaces(),
            new \humhub\modules\admin\permissions\ManageSettings(),
        ]));
        $this->addEntry($entry);

        $entry = new MenuEntry();
        $entry->setLabel(Yii::t('AdminModule.widgets_AdminMenuWidget', 'Modules'));
        $entry->setUrl(['/admin/module']);
        $entry->setIcon(new Icon(['name' => 'rocket']));
        $entry->setSortOrder(500);
        $entry->setIsActive((Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'module'));
        $entry->setIsVisible(Yii::$app->user->can(new \humhub\modules\admin\permissions\ManageModules()));
        $this->addEntry($entry);

        $entry = new MenuEntry();
        $entry->setLabel(Yii::t('AdminModule.widgets_AdminMenuWidget', 'Settings'));
        $entry->setUrl(['/admin/setting']);
        $entry->setIcon(new Icon(['name' => 'gears']));
        $entry->setSortOrder(600);
        $entry->setIsActive((Yii::$app->controller->module && Yii::$app->controller->module->id == 'setting'));
        $entry->setIsVisible(Yii::$app->user->can(new \humhub\modules\admin\permissions\ManageSettings()));
        $this->addEntry($entry);

        $entry = new MenuEntry();
        $entry->setLabel(Yii::t('AdminModule.widgets_AdminMenuWidget', 'Information'));
        $entry->setUrl(['/admin/information']);
        $entry->setIcon(new Icon(['name' => 'info-circle']));
        $entry->setSortOrder(1000);
        $entry->setIsActive((Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'information'));
        $entry->setIsVisible(Yii::$app->user->can(new \humhub\modules\admin\permissions\SeeAdminInformation()));
        $this->addEntry($entry);

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
