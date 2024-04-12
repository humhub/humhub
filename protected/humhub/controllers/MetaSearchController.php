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

        $params = Yii::$app->request->post('params');

        return MetaSearchProviderWidget::widget([
            'provider' => Yii::$app->request->post('provider'),
            'params' => is_array($params) ? $params : [],
            'keyword' => Yii::$app->request->post('keyword'),
        ]);
    }
}
