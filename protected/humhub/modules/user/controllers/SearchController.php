<?php

namespace humhub\modules\user\controllers;

use Yii;
use yii\web\Controller;
use yii\helpers\Html;
use humhub\modules\user\models\User;

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

        $query = User::find()->limit($maxResults)->joinWith('profile');

        foreach (explode(" ", $keyword) as $part) {
            $query->orFilterWhere(['like', 'user.email', $part]);
            $query->orFilterWhere(['like', 'user.username', $part]);
            $query->orFilterWhere(['like', 'profile.firstname', $part]);
            $query->orFilterWhere(['like', 'profile.lastname', $part]);
            $query->orFilterWhere(['like', 'profile.title', $part]);
        }

        $query->active();
        
        $results = [];
        foreach ($query->all() as $user) {
            if ($user != null) {
                $userInfo = array();
                $userInfo['guid'] = $user->guid;
                $userInfo['displayName'] = Html::encode($user->displayName);
                $userInfo['image'] = $user->getProfileImage()->getUrl();
                $userInfo['link'] = $user->getUrl();
                $results[] = $userInfo;
            }
        }

        
        return $results;
    }

}

?>
