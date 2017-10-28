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
        $this->addItem([
            'label' => Yii::t('AdminModule.user', 'Settings'),
            'url' => Url::toRoute(['/admin/group/edit', 'id' => $this->group->id]),
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'group' && Yii::$app->controller->action->id == 'edit'),
        ]);
        $this->addItem([
            'label' => Yii::t('AdminModule.user', "Permissions"),
            'url' => Url::toRoute(['/admin/group/manage-permissions', 'id' => $this->group->id]),
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'group' && Yii::$app->controller->action->id == 'manage-permissions'),
        ]);
        $this->addItem([
            'label' => Yii::t('AdminModule.user', "Members"),
            'url' => Url::toRoute(['/admin/group/manage-group-users', 'id' => $this->group->id]),
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'group' && Yii::$app->controller->action->id == 'manage-group-users'),
        ]);

        parent::init();
    }

}
