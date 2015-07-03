<?php

namespace humhub\modules\admin\controllers;

use Yii;
use yii\helpers\Url;
use humhub\components\Controller;

/**
 * @package humhub.modules_core.admin.controllers
 * @since 0.5
 */
class IndexController extends Controller
{

    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
                'adminOnly' => true
            ]
        ];
    }

    /**
     * List all available user groups
     */
    public function actionIndex()
    {
        return Yii::$app->response->redirect(Url::toRoute('/admin/setting'));
    }

}
