<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\modules\ui\menu\MenuEntry;
use Yii;
use yii\helpers\Url;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\admin\permissions\ManageSpaces;
use humhub\modules\ui\menu\widgets\TabMenu;

/**
 * Space Administration Menu
 *
 * @author Luke
 */
class SpaceMenu extends TabMenu
{
    /**
     * @inheritdoc
     */
    public function init()
    {

        $this->addItem([
            'label' => Yii::t('AdminModule.views_space_index', 'Spaces'),
            'url' => Url::toRoute(['/admin/space/index']),
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'space' && Yii::$app->controller->action->id == 'index'),
            'isVisible' => Yii::$app->user->can(new ManageSpaces())
        ]);

        $this->addItem([
            'label' => Yii::t('AdminModule.views_space_index', 'Settings'),
            'url' => Url::toRoute(['/admin/space/settings']),
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'space' && Yii::$app->controller->action->id == 'settings'),
            'isVisible' => Yii::$app->user->can(new ManageSettings())
        ]);

        parent::init();
    }

}
