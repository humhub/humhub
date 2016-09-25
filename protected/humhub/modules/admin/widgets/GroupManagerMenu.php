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
class GroupManagerMenu extends \humhub\widgets\BaseMenu
{

    /**
     * @inheritdoc
     */
    public $template = "@humhub/widgets/views/subTabMenu";

    /**
     * @var \humhub\modules\user\models\Group
     */
    public $group;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->addItem(array(
            'label' => Yii::t('AdminModule.views_user_index', 'Settings'),
            'url' => Url::toRoute(['/admin/group/edit', 'id' => $this->group->id]),
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'group' && Yii::$app->controller->action->id == 'edit'),
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.views_groups_index', "Permissions"),
            'url' => Url::toRoute(['/admin/group/manage-permissions', 'id' => $this->group->id]),
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'group' && Yii::$app->controller->action->id == 'manage-permissions'),
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.views_groups_index', "Members"),
            'url' => Url::toRoute(['/admin/group/manage-group-users', 'id' => $this->group->id]),
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'group' && Yii::$app->controller->action->id == 'manage-group-users'),
        ));

        parent::init();
    }

}
