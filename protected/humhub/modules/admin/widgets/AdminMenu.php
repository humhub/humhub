<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\modules\admin\permissions\ManageCategories;
use humhub\modules\admin\permissions\ManageChallenges;
use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\admin\permissions\ManageMarketplaces;
use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\admin\permissions\ManageSpaces;
use humhub\modules\admin\permissions\ManageTags;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\admin\permissions\SeeAdminInformation;
use humhub\widgets\BaseMenu;
use Yii;
use yii\helpers\Url;

/**
 * Description of AdminMenu
 *
 * @author luke
 */
class AdminMenu extends BaseMenu
{

    const SESSION_CAN_SEE_ADMIN_SECTION = 'user.canSeeAdminSection';

    public $template = "@humhub/widgets/views/leftNavigation";
    public $type = "adminNavigation";
    public $id = "admin-menu";

    public function init()
    {
        $this->addItemGroup([
            'id' => 'admin',
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', '<strong>Administration</strong> menu'),
            'sortOrder' => 100,
        ]);

        $this->addItem([
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Users'),
            'url' => Url::toRoute(['/admin/user']),
            'icon' => '<i class="fa fa-user"></i>',
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && (Yii::$app->controller->id == 'user' || Yii::$app->controller->id == 'group' || Yii::$app->controller->id == 'approval' || Yii::$app->controller->id == 'authentication' || Yii::$app->controller->id == 'user-profile' || Yii::$app->controller->id == 'pending-registrations')),
            'isVisible' => Yii::$app->user->can([
                new ManageUsers(),
                new ManageSettings(),
                new ManageGroups()
            ]),
        ]);

        $this->addItem([
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Spaces'),
            'id' => 'spaces',
            'url' => Url::toRoute('/admin/space'),
            'icon' => '<i class="fa fa-inbox"></i>',
            'sortOrder' => 300,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'space'),
            'isVisible' => Yii::$app->user->can([
                new ManageSpaces(),
                new ManageSettings(),
            ]),
        ]);

        if (Yii::$app->hasModule('xcoin')) {
            $this->addItem([
                'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Tags'),
                'id' => 'tags',
                'url' => Url::toRoute('/admin/tag/index-user'),
                'icon' => '<i class="fa fa-tag"></i>',
                'sortOrder' => 400,
                'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'tag'),
                'isVisible' => Yii::$app->user->can([
                    new ManageTags(),
                ]),
            ]);
            $this->addItem([
                'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Categories'),
                'id' => 'categories',
                'url' => Url::toRoute('/admin/category/index-funding'),
                'icon' => '<i class="fa fa-bookmark"></i>',
                'sortOrder' => 400,
                'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'category'),
                'isVisible' => Yii::$app->user->can([
                    new ManageCategories(),
                ]),
            ]);
            $this->addItem([
                'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Challenges'),
                'id' => 'challenges',
                'url' => Url::toRoute('/admin/challenge/index'),
                'icon' => '<i class="fa fa-users"></i>',
                'sortOrder' => 400,
                'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'challenge'),
                'isVisible' => Yii::$app->user->can([
                    new ManageChallenges(),
                ]),
            ]);
            $this->addItem([
                'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Marketplaces'),
                'id' => 'marketplaces',
                'url' => Url::toRoute('/admin/marketplace/index'),
                'icon' => '<i class="fa fa-shopping-basket"></i>',
                'sortOrder' => 400,
                'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'marketplace'),
                'isVisible' => Yii::$app->user->can([
                    new ManageMarketplaces(),
                ]),
            ]);
        }

        $this->addItem([
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Modules'),
            'id' => 'modules',
            'url' => Url::toRoute('/admin/module'),
            'icon' => '<i class="fa fa-rocket"></i>',
            'sortOrder' => 500,
            'newItemCount' => 0,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'module'),
            'isVisible' => Yii::$app->user->can(new ManageModules())
        ]);

        $this->addItem([
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Settings'),
            'url' => Url::toRoute('/admin/setting'),
            'icon' => '<i class="fa fa-gears"></i>',
            'sortOrder' => 600,
            'newItemCount' => 0,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'setting'),
            'isVisible' => Yii::$app->user->can(new ManageSettings())
        ]);

        $this->addItem([
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Information'),
            'url' => Url::toRoute('/admin/information'),
            'icon' => '<i class="fa fa-info-circle"></i>',
            'sortOrder' => 10000,
            'newItemCount' => 0,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'information'),
            'isVisible' => Yii::$app->user->can(new SeeAdminInformation())
        ]);

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

    public function addItem($item)
    {
        $item['group'] = 'admin';

        parent::addItem($item);
    }

    public static function canAccess()
    {
        $canSeeAdminSection = Yii::$app->session->get(static::SESSION_CAN_SEE_ADMIN_SECTION);
        if ($canSeeAdminSection == null) {
            $canSeeAdminSection = Yii::$app->user->isAdmin() ? true : self::checkNonAdminAccess();
            Yii::$app->session->set(static::SESSION_CAN_SEE_ADMIN_SECTION, $canSeeAdminSection);
        }

        return $canSeeAdminSection;
    }

    public static function reset()
    {
        Yii::$app->session->remove(static::SESSION_CAN_SEE_ADMIN_SECTION);
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
