<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\controllers;

use humhub\components\Controller;
use humhub\modules\content\Module;
use humhub\modules\content\search\ResultSet;
use humhub\modules\content\search\SearchRequest;
use humhub\modules\content\widgets\stream\StreamEntryWidget;
use humhub\modules\content\widgets\stream\WallStreamEntryOptions;
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
        return $this->render('index');
    }

    public function actionResults()
    {
        $resultSet = null;

        $this->searchRequest = new SearchRequest(['pageSize' => 3]);
        if ($this->searchRequest->load(Yii::$app->request->get(), '') && $this->searchRequest->validate()) {
            $resultSet = $this->module->getSearchDriver()->searchCached($this->searchRequest, 10);
        }

        $page = $resultSet ? $resultSet->pagination->getPage() + 1 : 1;
        $totalCount = $resultSet ? $resultSet->pagination->totalCount : 0;
        $results = $this->renderResults($resultSet);

        return $this->asJson([
            'content' => $page > 1
                ? $results
                : $this->renderAjax('results', ['results' => $results, 'totalCount' => $totalCount]),
            'page' => $page,
            'isLast' => $results === '' || !$resultSet || $page === $resultSet->pagination->getPageCount(),
        ]);
    }

    private function renderResults($resultSet): ?string
    {
        if (!($resultSet instanceof ResultSet)) {
            return null;
        }

        $results = '';
        $options = (new WallStreamEntryOptions())->viewContext(WallStreamEntryOptions::VIEW_CONTEXT_SEARCH);
        foreach ($resultSet->results as $result) {
            $results .= StreamEntryWidget::renderStreamEntry($result->getModel(), $options);
        }

        return $results;
    }
}
