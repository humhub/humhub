<?php

/**
 * BrowseController
 *
 * @author Luke
 * @package humhub.modules_core.space.controllers
 * @since 0.5
 */
class BrowseController extends Controller {

    public $subLayout = "application.modules_core.space.views.browse._layout";

    /**
     * @return array action filters
     */
    public function filters() {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
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
    public function actionSearchJson() {
        $keyword = Yii::app()->request->getParam('keyword', ""); // guid of user/workspace
        $page = (int) Yii::app()->request->getParam('page', 1); // current page (pagination)
        $limit = (int) Yii::app()->request->getParam('limit', HSetting::Get('paginationSize')); // current page (pagination)
        $keyword = Yii::app()->input->stripClean($keyword);
        $hitCount = 0;

        $query = "model:Space ";
        if (strlen($keyword) > 2) {

            // Include Keyword
            if (strpos($keyword, "@") === false) {
                $keyword = str_replace(".", "", $keyword);
                $query .= "AND (title:" . $keyword . "* OR tags:" . $keyword . "*)";
            }
        }

        //, $limit, $page
        $hits = new ArrayObject(
                HSearch::getInstance()->Find($query
        ));

        $hitCount = count($hits);

        // Limit Hits
        $hits = new LimitIterator($hits->getIterator(), ($page - 1) * $limit, $limit);


        $json = array();
        #$json['totalHits'] = $hitCount;
        #$json['limit'] = $limit;
        #$results = array();
        foreach ($hits as $hit) {
            $doc = $hit->getDocument();
            $model = $doc->getField("model")->value;

            if ($model == "Space") {
                $workspaceId = $doc->getField('pk')->value;
                $workspace = Space::model()->findByPk($workspaceId);

                if ($workspace != null) {
                    $wsInfo = array();
                    $wsInfo['guid'] = $workspace->guid;
                    $wsInfo['title'] = CHtml::encode($workspace->name);
                    $wsInfo['tags'] = CHtml::encode($workspace->tags);
                    $wsInfo['image'] = $workspace->getProfileImage()->getUrl();
                    $wsInfo['link'] = $workspace->getUrl();
                    #$results[] = $wsInfo;
                    $json[] = $wsInfo;
                } else {
                    Yii::log("Could not load workspace with id " . $userId . " from search index!", CLogger::LEVEL_ERROR);
                }
            } else {
                Yii::log("Got no workspace hit from search index!", CLogger::LEVEL_ERROR);
            }
        }
        #$json['results'] = $results;

        print CJSON::encode($json);
        Yii::app()->end();
    }

}

?>