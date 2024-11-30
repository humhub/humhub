<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\controllers;

use humhub\components\Controller;
use humhub\widgets\MetaSearchProviderWidget;
use Yii;

/**
 * @since 1.16
 */
class MetaSearchController extends Controller
{
    public function actionIndex()
    {
        $this->forcePostRequest();

        return MetaSearchProviderWidget::widget([
            'provider' => Yii::$app->request->post('provider'),
            'route' => Yii::$app->request->post('route'),
            'keyword' => Yii::$app->request->post('keyword'),
        ]);
    }
}
