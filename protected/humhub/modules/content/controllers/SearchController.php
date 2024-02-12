<?php

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

    /**
     * @note The current search request, required for File highlighting
     */
    public ?SearchRequest $searchRequest = null;

    public function actionIndex()
    {
        $resultSet = null;

        $this->searchRequest = new SearchRequest();
        if ($this->searchRequest->load(Yii::$app->request->get(), '') && $this->searchRequest->validate()) {
            $resultSet = $this->module->getSearchDriver()->search($this->searchRequest);
        }

        return $this->render('index', [
            'searchRequest' => $this->searchRequest,
            'resultSet' => $resultSet,
        ]);
    }
}
