<?php

namespace humhub\core\dashboard\controllers;

use Yii;
use yii\web\Controller;
use humhub\models\Setting;

class DashboardController extends Controller
{

    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
                'guestAllowedActions' => ['index', 'stream']
            ]
        ];
    }

    public function actions()
    {
        return [
            'stream' => [
                'class' => \humhub\core\dashboard\components\actions\DashboardStream::className(),
            ],
        ];
    }

    /**
     * Dashboard Index
     *
     * Show recent wall entries for this user
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->render('index_guest', array());
        } else {
            return $this->render('index', array('showProfilePostForm' => Setting::Get('showProfilePostForm', 'dashboard')));
        }
    }

}
