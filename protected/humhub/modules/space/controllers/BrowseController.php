<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\controllers;

use humhub\components\access\ControllerAccess;
use humhub\modules\space\models\Membership;
use humhub\modules\space\widgets\Chooser;
use Yii;
use humhub\components\Controller;
use yii\data\Pagination;

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
                'class' => \humhub\components\behaviors\AccessControl::className(),
                'guestAllowedActions' => ['search-json'],
                'rules' => [
                    [ControllerAccess::RULE_LOGGED_IN_ONLY => ['chooser-json']],
                ],
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
        \Yii::$app->response->format = 'json';

        $keyword = Yii::$app->request->get('keyword', "");
        $page = (int) Yii::$app->request->get('page', 1);
        $limit = (int) Yii::$app->request->get('limit', Yii::$app->settings->get('paginationSize'));

        $searchResultSet = Yii::$app->search->find($keyword, [
            'model' => \humhub\modules\space\models\Space::className(),
            'page' => $page,
            'pageSize' => $limit
        ]);

        return $this->prepareResult($searchResultSet);
    }

    /**
     * Returns a workspace list by json, implements pagination for the space chooser.
     */
    public function actionChooserJson()
    {
        \Yii::$app->response->format = 'json';

        // paginates the result based on 'page' query parameter
        $pagination = new Pagination([
            'pageSize' => Chooser::SPACE_BATCH_SIZE,
            'totalCount' => Membership::findByUser(Yii::$app->user->getIdentity())->count(),
        ]);
        $memberships = Membership::findByUser(Yii::$app->user->getIdentity())->limit($pagination->limit)->offset($pagination->offset)->all();

        $items = [];
        foreach ($memberships as $membership) {
            $items[] = \humhub\modules\space\widgets\Chooser::getSpaceResult($membership->space, true, ['isMember' => true]);
        }
        return [
            'items' => $items,
            'page' => $pagination->page + 1,
            'lastPage' => $pagination->page + 1 >= $pagination->pageCount,
        ];
    }

    protected function prepareResult($searchResultSet)
    {
        $target = Yii::$app->request->get('target');
        
        $json = [];
        $withChooserItem = ($target === 'chooser');
        foreach ($searchResultSet->getResultInstances() as $space) {
            $json[] = \humhub\modules\space\widgets\Chooser::getSpaceResult($space, $withChooserItem);
        }

        return $json;
    }

}
