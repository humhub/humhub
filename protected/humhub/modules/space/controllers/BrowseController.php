<?php

namespace humhub\modules\space\controllers;

use Yii;
use \humhub\components\Controller;
use \yii\helpers\Url;
use \yii\web\HttpException;
use \humhub\modules\user\models\User;
use yii\helpers\Html;

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
     * Returns a workspace list by json
     *
     * It can be filtered by by keyword.
     */
    public function actionSearchJson()
    {
        \Yii::$app->response->format = 'json';

        $keyword = Yii::$app->request->get('keyword', "");
        $page = (int) Yii::$app->request->get('page', 1);
        $limit = (int) Yii::$app->request->get('limit', \humhub\models\Setting::Get('paginationSize'));

        $searchResultSet = Yii::$app->search->find($keyword, [
            'model' => 'Space',
            'page' => $page,
            'pageSize' => $limit
        ]);

        $json = array();
        foreach ($searchResultSet->getResultInstances() as $space) {
            $spaceInfo = array();
            $spaceInfo['guid'] = $space->guid;
            $spaceInfo['title'] = Html::encode($space->name);
            $spaceInfo['tags'] = Html::encode($space->tags);
            $spaceInfo['image'] = $space->getProfileImage()->getUrl();
            $spaceInfo['link'] = $space->getUrl();

            $json[] = $spaceInfo;
        }

        return $json;
    }

}

?>