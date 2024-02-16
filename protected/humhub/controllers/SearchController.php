<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\controllers;

use humhub\components\Controller;
use humhub\widgets\SearchProvider;
use Yii;

/**
 * @since 1.16
 */
class SearchController extends Controller
{
    public function actionIndex()
    {
        $this->forcePostRequest();

        return SearchProvider::widget([
            'searchProvider' => Yii::$app->request->post('provider'),
            'keyword' => Yii::$app->request->post('keyword')
        ]);
    }
}
