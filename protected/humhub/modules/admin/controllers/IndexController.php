<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use Yii;
use yii\helpers\Url;
use humhub\modules\admin\components\Controller;

/**
 * IndexController is the Admin section start point.
 * 
 * @since 0.5
 */
class IndexController extends Controller
{

    /**
     * List all available user groups
     */
    public function actionIndex()
    {
        return Yii::$app->response->redirect(Url::toRoute('/admin/setting'));
    }

}
