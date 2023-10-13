<?php

namespace humhub\modules\content\controllers;

use humhub\components\Controller;
use humhub\modules\content\Module;
use humhub\modules\content\search\SearchRequest;
use humhub\modules\content\services\ContentSearchService;
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
        $resultSet = null;

        $searchRequest = new SearchRequest();
        if ($searchRequest->load(Yii::$app->request->get(), '') && $searchRequest->validate()) {
            $resultSet = $this->module->getSearchDriver()->search($searchRequest);
        }

        return $this->render('index', [
            'searchOptions' => $searchRequest,
            'resultSet' => $resultSet,
        ]);
    }
}
