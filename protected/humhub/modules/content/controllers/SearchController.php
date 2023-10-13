<?php

namespace humhub\modules\content\controllers;

use humhub\components\Controller;
use humhub\modules\content\search\driver\ZendLucenceDriver;
use humhub\modules\content\search\SearchRequest;
use Yii;

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
            $resultSet = (new ZendLucenceDriver())->search($searchRequest);
        }

        return $this->render('index', [
            'searchOptions' => $searchRequest,
            'resultSet' => $resultSet,
        ]);
    }
}
