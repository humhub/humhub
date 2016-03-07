<?php

namespace humhub\modules\user\controllers;

use Yii;
use yii\web\Controller;
use yii\helpers\Html;
use humhub\modules\user\models\UserFilter;

/**
 * Search Controller provides action for searching users.
 *
 * @author Luke
 * @package humhub.modules_core.user.controllers
 * @since 0.5
 */
class SearchController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
            ]
        ];
    }

    /**
     * JSON Search for Users
     *
     * Returns an array of users with fields:
     *  - guid
     *  - displayName
     *  - image
     *  - profile link
     */
    public function actionJson()
    {
        Yii::$app->response->format = 'json';

        $maxResults = 10;
        $keyword = Yii::$app->request->get('keyword');
        $userRole = Yii::$app->request->get('userRole');
        
        $friendsOnly = ($userRole != null && $userRole == User::USERGROUP_FRIEND);
        return UserFilter::forUser()->getUserPickerResult($keyword, $maxResults, $friendsOnly);
    }

}

?>
