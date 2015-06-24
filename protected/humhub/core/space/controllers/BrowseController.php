<?php

/**
 * BrowseController
 *
 * @author Luke
 * @package humhub.modules_core.space.controllers
 * @since 0.5
 */
class BrowseController extends Controller
{

    public $subLayout = "application.modules_core.space.views.browse._layout";

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
                'users' => array('@'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Returns a workspace list by json
     *
     * It can be filtered by by keyword.
     */
    public function actionSearchJson()
    {

        $keyword = Yii::app()->request->getParam('keyword', "");
        $page = (int) Yii::app()->request->getParam('page', 1);
        $limit = (int) Yii::app()->request->getParam('limit', HSetting::Get('paginationSize'));

        $searchResultSet = Yii::app()->search->find($keyword, [
            'model' => 'Space',
            'page' => $page,
            'pageSize' => $limit
        ]);

        $json = array();
        foreach ($searchResultSet->getResultInstances() as $space) {
            $spaceInfo = array();
            $spaceInfo['guid'] = $space->guid;
            $spaceInfo['title'] = CHtml::encode($space->name);
            $spaceInfo['tags'] = CHtml::encode($space->tags);
            $spaceInfo['image'] = $space->getProfileImage()->getUrl();
            $spaceInfo['link'] = $space->getUrl();

            $json[] = $spaceInfo;
        }

        print CJSON::encode($json);
        Yii::app()->end();
    }

}

?>