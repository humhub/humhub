<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 * Search Controller provides search functions inside the application.
 *
 * @author Luke
 * @package humhub.modules_core.search.controllers
 * @since 0.12
 */
class SearchController extends Controller
{

    const SCOPE_ALL = "all";
    const SCOPE_USER = "user";
    const SCOPE_SPACE = "space";
    const SCOPE_CONTENT = "content";

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'users' => array('@', (HSetting::Get('allowGuestAccess', 'authentication_internal')) ? "?" : "@"),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    public function actionIndex()
    {
        $keyword = Yii::app()->request->getParam('keyword', "");
        $scope = Yii::app()->request->getParam('scope', "");
        $page = (int) Yii::app()->request->getParam('page', 1);
        $limitSpaceGuids = Yii::app()->request->getParam('limitSpaceGuids', "");

        $limitSpaces = array();
        if ($limitSpaceGuids !== "") {
            foreach (explode(",", $limitSpaceGuids) as $guid) {
                $guid = trim($guid);
                if ($guid != "") {
                    $space = Space::model()->findByAttributes(array('guid' => trim($guid)));
                    if ($space !== null) {
                        $limitSpaces[] = $space;
                    }
                }
            }
        }

        $options = [
            'page' => $page,
            'sort' => ($keyword == '') ? 'title' : null,
            'pageSize' => HSetting::Get('paginationSize'),
            'limitSpaces' => $limitSpaces
        ];
        if ($scope == self::SCOPE_CONTENT) {
            $options['type'] = 'Content';
        } elseif ($scope == self::SCOPE_SPACE) {
            $options['model'] = 'Space';
        } elseif ($scope == self::SCOPE_USER) {
            $options['model'] = 'User';
        } else {
            $scope = self::SCOPE_ALL;
        }

        $searchResultSet = Yii::app()->search->find($keyword, $options);

        // Create Pagination Class
        $pagination = new CPagination($searchResultSet->total);
        $pagination->setPageSize($searchResultSet->pageSize);

        $this->render('index', array(
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
        $results = array();
        $keyword = Yii::app()->request->getParam('keyword', "");

        $searchResultSet = Yii::app()->search->find($keyword, [
            'model' => array('User', 'Space'),
            'pageSize' => 10
        ]);

        foreach ($searchResultSet->getResultInstances() as $container) {
            $results[] = array(
                'guid' => $container->guid,
                'type' => ($container instanceof Space) ? "s" : "u",
                'name' => $container->getDisplayName(),
                'image' => $container->getProfileImage()->getUrl(),
                'link' => $container->getUrl()
            );
        }

        echo CJSON::encode($results);
        Yii::app()->end();
    }

    protected function getTotals($keyword, $options)
    {
        $totals = array();

        // Unset unnecessary search options
        unset($options['model']);
        unset($options['type']);
        unset($options['page']);
        unset($options['pageSize']);

        $searchResultSetCount = Yii::app()->search->find($keyword, array_merge($options, ['model' => 'User']));
        $totals[self::SCOPE_USER] = $searchResultSetCount->total;
        $searchResultSetCount = Yii::app()->search->find($keyword, array_merge($options, ['model' => 'Space']));
        $totals[self::SCOPE_SPACE] = $searchResultSetCount->total;

        $searchResultSetCount = Yii::app()->search->find($keyword, array_merge($options, ['type' => 'Content']));
        $totals[self::SCOPE_CONTENT] = $searchResultSetCount->total;
        $totals[self::SCOPE_ALL] = $totals[self::SCOPE_CONTENT] + $totals[self::SCOPE_SPACE] + $totals[self::SCOPE_USER];

        return $totals;
    }

}
