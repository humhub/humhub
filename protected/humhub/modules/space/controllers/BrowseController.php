<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\controllers;

use humhub\components\Controller;
use humhub\components\behaviors\AccessControl;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Chooser;
use Yii;

/**
 * BrowseController
 *
 * @author Luke
 * @package humhub.modules_core.space.controllers
 * @since 0.5
 */
class BrowseController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => AccessControl::class,
                'guestAllowedActions' => ['search-json']
            ]
        ];
    }

    /**
     * Returns a workspace list by json
     *
     * It can be filtered by by keyword.
     */
    public function actionSearchJson()
    {
        Yii::$app->response->format = 'json';

        $keyword = Yii::$app->request->get('keyword', '');
        $page = (int) Yii::$app->request->get('page', 1);
        $limit = (int) Yii::$app->request->get('limit', Yii::$app->settings->get('paginationSize'));

        $searchResultSet = Yii::$app->search->find($keyword, [
            'model' => Space::class,
            'page' => $page,
            'pageSize' => $limit
        ]);

        return $this->prepareResult($searchResultSet);
    }

    protected function prepareResult($searchResultSet)
    {
        $target = Yii::$app->request->get('target');
        
        $json = [];
        $withChooserItem = ($target === 'chooser');
        foreach ($searchResultSet->getResultInstances() as $space) {
            $json[] = Chooser::getSpaceResult($space, $withChooserItem);
        }

        return $json;
    }

}
