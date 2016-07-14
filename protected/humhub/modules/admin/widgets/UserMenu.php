<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use Yii;
use yii\helpers\Url;
use humhub\models\Setting;

/**
 * User Administration Menu
 *
 * @author Basti
 */
class UserMenu extends \humhub\widgets\BaseMenu
{

    public $template = "@humhub/widgets/views/tabMenu";
    public $type = "adminUserSubNavigation";

    public function init()
    {

        $this->addItem(array(
            'label' => Yii::t('AdminModule.views_user_index', 'Overview'),
            'url' => Url::toRoute(['/admin/user/index']),
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'user' && Yii::$app->controller->action->id == 'index'),
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.views_user_index', 'Add new user'),
            'url' => Url::toRoute(['/admin/user/add']),
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'user' && Yii::$app->controller->action->id == 'add'),
        ));

        parent::init();
    }

}
