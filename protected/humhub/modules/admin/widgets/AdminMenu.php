<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use Yii;
use yii\helpers\Url;

/**
 * Description of AdminMenu
 *
 * @author luke
 */
class AdminMenu extends \humhub\widgets\BaseMenu
{

    public $template = "@humhub/widgets/views/leftNavigation";
    public $type = "adminNavigation";
    public $id = "admin-menu";

    public function init()
    {
        $this->addItemGroup([
            'id' => 'admin',
            'label' => \Yii::t('AdminModule.widgets_AdminMenuWidget', '<strong>Administration</strong> menu'),
            'sortOrder' => 100,
        ]);

        $this->addItem([
            'label' => \Yii::t('AdminModule.widgets_AdminMenuWidget', 'Users'),
            'url' => Url::toRoute(['/admin/user']),
            'icon' => '<i class="fa fa-user"></i>',
            'sortOrder' => 200,
            'isActive' => (\Yii::$app->controller->module && \Yii::$app->controller->module->id == 'admin' && (Yii::$app->controller->id == 'user' || Yii::$app->controller->id == 'group' || Yii::$app->controller->id == 'approval' || Yii::$app->controller->id == 'authentication' || Yii::$app->controller->id == 'user-profile')),
            'isVisible' => Yii::$app->user->can([
                new \humhub\modules\admin\permissions\ManageUsers(),
                new \humhub\modules\admin\permissions\ManageSettings(),
                new \humhub\modules\admin\permissions\ManageGroups()
            ]),
        ]);

        $this->addItem([
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Spaces'),
            'id' => 'spaces',
            'url' => Url::toRoute('/admin/space'),
            'icon' => '<i class="fa fa-inbox"></i>',
            'sortOrder' => 400,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'space'),
            'isVisible' => Yii::$app->user->can([
                new \humhub\modules\admin\permissions\ManageSpaces(),
                new \humhub\modules\admin\permissions\ManageSettings(),
            ]),
        ]);

        $this->addItem([
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Modules'),
            'id' => 'modules',
            'url' => Url::toRoute('/admin/module'),
            'icon' => '<i class="fa fa-rocket"></i>',
            'sortOrder' => 500,
            'newItemCount' => 0,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'module'),
            'isVisible' => Yii::$app->user->can(new \humhub\modules\admin\permissions\ManageModules())
        ]);

        $this->addItem([
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Settings'),
            'url' => Url::toRoute('/admin/setting'),
            'icon' => '<i class="fa fa-gears"></i>',
            'sortOrder' => 600,
            'newItemCount' => 0,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'setting'),
            'isVisible' => Yii::$app->user->can(new \humhub\modules\admin\permissions\ManageSettings())
        ]);

        $this->addItem([
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Information'),
            'url' => Url::toRoute('/admin/information'),
            'icon' => '<i class="fa fa-info-circle"></i>',
            'sortOrder' => 10000,
            'newItemCount' => 0,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'information'),
            'isVisible' => Yii::$app->user->can(new \humhub\modules\admin\permissions\SeeAdminInformation())
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
        foreach($adminMenu->items as $item) {
            if(isset($item['isVisible']) && $item['isVisible']) {
                return true;
            }
        }

		return false;
    }

}
