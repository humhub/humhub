<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\controllers;

use humhub\components\Controller;
use humhub\modules\content\Module;
use humhub\modules\content\search\SearchRequest;
use Yii;

/**
 * @property Module $module
 */
class SearchController extends Controller
{

    /**
     * @inheritdoc
     */
    public $subLayout = '@content/views/search/_layout';

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionResults()
    {
        $resultSet = null;

        $searchRequest = new SearchRequest();
        if ($searchRequest->load(Yii::$app->request->get(), '') && $searchRequest->validate()) {
            $resultSet = $this->module->getSearchDriver()->search($searchRequest);
        }

        return $this->renderAjax('results', [
            'searchRequest' => $searchRequest,
            'resultSet' => $resultSet,
        ]);
    }
}
