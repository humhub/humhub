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
class InformationMenu extends \humhub\widgets\BaseMenu
{

    /**
     * @inheritdoc
     */
    public $template = "@humhub/widgets/views/tabMenu";

    public function init()
    {
        $this->addItem([
            'label' => Yii::t('AdminModule.information', 'About HumHub'),
            'url' => Url::to(['/admin/information/about']),
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'information' && Yii::$app->controller->action->id == 'about'),
        ]);

        $this->addItem([
            'label' => Yii::t('AdminModule.information', 'Prerequisites'),
            'url' => Url::to(['/admin/information/prerequisites']),
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'information' && Yii::$app->controller->action->id == 'prerequisites'),
        ]);

        $this->addItem([
            'label' => Yii::t('AdminModule.information', 'Database'),
            'url' => Url::to(['/admin/information/database']),
            'sortOrder' => 300,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'information' && Yii::$app->controller->action->id == 'database'),
        ]);

        $this->addItem([
            'label' => Yii::t('AdminModule.information', 'CronJobs'),
            'url' => Url::to(['/admin/information/cronjobs']),
            'sortOrder' => 400,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'information' && Yii::$app->controller->action->id == 'cronjobs'),
        ]);

        $this->addItem([
            'label' => Yii::t('AdminModule.information', 'Logging'),
            'url' => Url::toRoute(['/admin/logging']),
            'sortOrder' => 500,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'logging'),
        ]);

        parent::init();
    }

}
