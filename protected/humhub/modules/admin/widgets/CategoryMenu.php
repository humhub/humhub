<?php
/**
 * @link https://coinsence.org/
 * @copyright Copyright (c) 2018 Coinsence
 * @license https://www.humhub.com/licences
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */

namespace humhub\modules\admin\widgets;

use humhub\widgets\BaseMenu;
use Yii;
use yii\helpers\Url;
use humhub\modules\admin\permissions\ManageSpaces;
use humhub\modules\admin\permissions\ManageSettings;

/**
 * Category Administration Menu
 */
class CategoryMenu extends BaseMenu
{

    public $template = "@humhub/widgets/views/tabMenu";
    public $type = "adminUserSubNavigation";

    public function init()
    {
        $this->addItem([
            'label' => Yii::t('AdminModule.views_space_index', 'Crowdfunding'),
            'url' => Url::toRoute(['/admin/category/index-funding']),
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'category' && Yii::$app->controller->action->id == 'index-funding'),
            'isVisible' => Yii::$app->user->can(new ManageSpaces())
        ]);
        $this->addItem([
            'label' => Yii::t('AdminModule.views_space_index', 'Marketplace'),
            'url' => Url::toRoute(['/admin/category/index-marketplace']),
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'category' && Yii::$app->controller->action->id == 'index-marketplace'),
            'isVisible' => Yii::$app->user->can(new ManageSettings())
        ]);

        parent::init();
    }

}
