<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use Yii;
use yii\helpers\Url;

/**
 * Group Administration Menu
 */
class SettingsMenu extends \humhub\widgets\BaseMenu
{

    /**
     * @inheritdoc
     */
    public $template = "@humhub/widgets/views/tabMenu";

    public function init()
    {

        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'General'),
            'url' => Url::toRoute('/admin/setting/index'),
            'icon' => '<i class="fa fa-cogs"></i>',
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'setting' && Yii::$app->controller->action->id == 'basic'),
            'isVisible' => Yii::$app->user->isAdmin(),
        ));
        
        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Appearance'),
            'url' => Url::toRoute('/admin/setting/design'),
            'icon' => '<i class="fa fa-magic"></i>',
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'setting' && Yii::$app->controller->action->id == 'design'),
            'isVisible' => Yii::$app->user->isAdmin(),
        ));

        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'E-Mails'),
            'url' => Url::toRoute('/admin/setting/mailing'),
            'icon' => '<i class="fa fa-envelope"></i>',
            'sortOrder' => 300,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'setting' && (Yii::$app->controller->action->id == 'mailing' || Yii::$app->controller->action->id == 'mailing-server')),
            'isVisible' => Yii::$app->user->isAdmin(),
        ));

        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Advanced'),
            'url' => Url::toRoute('/admin/setting/advanced'),
            'icon' => '<i class="fa fa-lock"></i>',
            'sortOrder' => 1000,
            'isVisible' => Yii::$app->user->isAdmin(),
        ));

        parent::init();
    }

}
