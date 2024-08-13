<?php

namespace  app\humhub\modules\prompt;

use Yii;
use yii\helpers\Url;

class Events
{
    /**
     * Defines what to do when the top menu is initialized.
     *
     * @param $event
     */
    public static function onTopMenuInit($event)
    {
        $event->sender->addItem([
            'label' => 'Prompt',
            'icon' => '<i class="fa fa-adjust"></i>',
            'url' => Url::to(['/prompt/index']),
            'sortOrder' => 99999,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'prompt' && Yii::$app->controller->id == 'index'),
        ]);
    }

    /**
     * Defines what to do if admin menu is initialized.
     *
     * @param $event
     */
    public static function onAdminMenuInit($event)
    {
        $event->sender->addItem([
            'label' => 'Prompt',
            'url' => Url::to(['/prompt/admin']),
            'group' => 'manage',
            'icon' => '<i class="fa fa-adjust"></i>',
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'prompt' && Yii::$app->controller->id == 'admin'),
            'sortOrder' => 99999,
        ]);
    }

    /**
     * Implement RESTful API for the module.
     *
     * @param $event
     */
    public static function onRestApiAddRules()
    {
        /**
         * @var \humhub\modules\rest\Module $restModule
         */
        $restModule = Yii::$app->getModule('rest');
        $restModule->addRules([
            ['pattern' => 'prompt', 'route' => 'prompt/rest/index', 'verb' => ['GET', 'HEAD']],
            ['pattern' => 'prompt/<id:\d+>', 'route' => 'prompt/rest/view', 'verb' => ['GET', 'HEAD']],
            ['pattern' => 'prompt', 'route' => 'prompt/rest/create', 'verb' => ['POST']],
            ['pattern' => 'prompt/<id:\d+>', 'route' => 'prompt/rest/update', 'verb' => ['PUT', 'PATCH']],
            ['pattern' => 'prompt/<id:\d+>', 'route' => 'prompt/rest/delete', 'verb' => ['DELETE']],
        ]);
    }
}
