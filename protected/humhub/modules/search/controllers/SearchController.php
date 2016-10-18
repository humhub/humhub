<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\search\controllers;

use Yii;
use humhub\components\Controller;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;

use humhub\modules\space\widgets\Image;

/**
 * Search Controller provides search functions inside the application.
 *
 * @author Luke
 * @since 0.12
 */
class SearchController extends Controller
{

    const SCOPE_ALL = "all";
    const SCOPE_USER = "user";
    const SCOPE_SPACE = "space";
    const SCOPE_CONTENT = "content";

    public function init()
    {
        $this->appendPageTitle(\Yii::t('SearchModule.base', 'Search'));
        return parent::init();
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
                'guestAllowedActions' => ['index']
            ]
        ];
    }

    public function actionIndex()
    {
        $keyword = Yii::$app->request->get('keyword', "");
        $scope = Yii::$app->request->get('scope', "");
        $page = (int)Yii::$app->request->get('page', 1);
        $limitSpaceGuids = Yii::$app->request->get('limitSpaceGuids', "");

        $limitSpaces = array();
        if ($limitSpaceGuids !== "") {
            foreach (explode(",", $limitSpaceGuids) as $guid) {
                $guid = trim($guid);
                if ($guid != "") {
                    $space = Space::findOne(['guid' => trim($guid)]);
                    if ($space !== null) {
                        $limitSpaces[] = $space;
                    }
                }
            }
        }

        $options = [
            'page' => $page,
            'sort' => ($keyword == '') ? 'title' : null,
            'pageSize' => Yii::$app->settings->get('paginationSize'),
            'limitSpaces' => $limitSpaces
        ];
        if ($scope == self::SCOPE_CONTENT) {
            $options['type'] = \humhub\modules\search\engine\Search::DOCUMENT_TYPE_CONTENT;
        } elseif ($scope == self::SCOPE_SPACE) {
            $options['model'] = Space::className();
        } elseif ($scope == self::SCOPE_USER) {
            $options['model'] = User::className();
        } else {
            $scope = self::SCOPE_ALL;
        }

        $searchResultSet = Yii::$app->search->find($keyword, $options);

        $pagination = new \yii\data\Pagination;
        $pagination->totalCount = $searchResultSet->total;
        $pagination->pageSize = $searchResultSet->pageSize;

        return $this->render('index', array(
            'scope' => $scope,
            'keyword' => $keyword,
            'results' => $searchResultSet->getResultInstances(),
            'pagination' => $pagination,
            'totals' => $this->getTotals($keyword, $options),
            'limitSpaceGuids' => $limitSpaceGuids
        ));
    }

    /**
     * JSON Search interface for Mentioning
     */
    public function actionMentioning()
    {
        \Yii::$app->response->format = 'json';

        $results = array();
        $keyword = Yii::$app->request->get('keyword', "");

        $searchResultSet = Yii::$app->search->find($keyword, [
            'model' => array(User::className(), Space::className()),
            'pageSize' => 10
        ]);

        foreach ($searchResultSet->getResultInstances() as $container) {
            $results[] = array(
                'guid' => $container->guid,
                'type' => ($container instanceof Space) ? "s" : "u",
                'name' => $container->getDisplayName(),
                'image' => ($container instanceof Space) ? Image::widget(['space' => $container, 'width' => 20]) : "<img class='img-rounded' src='" . $container->getProfileImage()->getUrl() . "' height='20' width='20' alt=''>",
                'link' => $container->getUrl()
            );
        };

        return $results;
    }

    protected function getTotals($keyword, $options)
    {
        $totals = array();

        // Unset unnecessary search options
        unset($options['model'], $options['type'], $options['page'], $options['pageSize']);

        $searchResultSetCount = Yii::$app->search->find($keyword, array_merge($options, ['model' => User::className()]));
        $totals[self::SCOPE_USER] = $searchResultSetCount->total;
        $searchResultSetCount = Yii::$app->search->find($keyword, array_merge($options, ['model' => Space::className()]));
        $totals[self::SCOPE_SPACE] = $searchResultSetCount->total;

        $searchResultSetCount = Yii::$app->search->find($keyword, array_merge($options, ['type' => \humhub\modules\search\engine\Search::DOCUMENT_TYPE_CONTENT]));
        $totals[self::SCOPE_CONTENT] = $searchResultSetCount->total;
        $totals[self::SCOPE_ALL] = $totals[self::SCOPE_CONTENT] + $totals[self::SCOPE_SPACE] + $totals[self::SCOPE_USER];

        return $totals;
    }

}
