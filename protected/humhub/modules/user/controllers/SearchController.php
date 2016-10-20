<?php

namespace humhub\modules\user\controllers;

use Yii;
use yii\web\Controller;


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
        
        return \humhub\modules\user\widgets\UserPicker::filter([
            'keyword' => Yii::$app->request->get('keyword'),
            'fillUser' => true,
            'disableFillUser' => false
        ]);
    }

}

?>
