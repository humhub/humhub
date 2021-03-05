<?php
/**
 * @link https://coinsence.org/
 * @copyright Copyright (c) 2020 Coinsence
 * @license https://www.humhub.com/licences
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */

namespace humhub\modules\admin\widgets;

use humhub\modules\admin\permissions\ManageTags;
use humhub\widgets\BaseMenu;
use Yii;
use yii\helpers\Url;

/**
 * Tag Administration Menu
 */
class TagMenu extends BaseMenu
{

    public $template = "@humhub/widgets/views/tabMenu";
    public $type = "adminUserSubNavigation";

    public function init()
    {
        $this->addItem([
            'label' => Yii::t('AdminModule.views_tag_index', 'User Tags'),
            'url' => Url::toRoute(['/admin/tag/index-user']),
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'tag' && Yii::$app->controller->action->id == 'index-user'),
            'isVisible' => Yii::$app->user->can(new ManageTags())
        ]);
        $this->addItem([
            'label' => Yii::t('AdminModule.views_tag_index', 'Space Tags'),
            'url' => Url::toRoute(['/admin/tag/index-space']),
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'tag' && Yii::$app->controller->action->id == 'index-space'),
            'isVisible' => Yii::$app->user->can(new ManageTags())
        ]);
        $this->addItem([
            'label' => Yii::t('AdminModule.views_tag_index', 'Tags - All'),
            'url' => Url::toRoute(['/admin/tag/index-all']),
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'tag' && Yii::$app->controller->action->id == 'index-all'),
            'isVisible' => Yii::$app->user->can(new ManageTags())
        ]);

        parent::init();
    }

}
