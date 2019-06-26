<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use Yii;
use yii\helpers\Url;

/**
 * Module Menu
 */
class ModuleMenu extends \humhub\widgets\BaseMenu
{

    /**
     * @inheritdoc
     */
    public $template = "@humhub/widgets/views/tabMenu";

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->addItem([
            'label' => Yii::t('AdminModule.module', 'Installed'),
            'url' => Url::to(['/admin/module/list']),
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'module' && Yii::$app->controller->action->id == 'list'),
        ]);

        parent::init();
    }

}
