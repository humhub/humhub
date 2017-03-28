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
class GroupMenu extends \humhub\widgets\BaseMenu
{

    /**
     * @inheritdoc
     */
    public $template = "@humhub/widgets/views/subTabMenu";

    public function init()
    {
        $this->addItem([
            'label' => Yii::t('AdminModule.views_user_index', 'Overview'),
            'url' => Url::toRoute(['/admin/group/index']),
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'group' && Yii::$app->controller->action->id == 'index'),
        ]);

        parent::init();
    }

    public function run()
    {
        if(count($this->getItemGroups()) > 1) {
            return parent::run();
        }
        return '';
    }

}
