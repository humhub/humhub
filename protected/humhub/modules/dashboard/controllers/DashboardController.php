<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */
namespace humhub\modules\dashboard\controllers;

use Yii;
use humhub\components\Controller;
use humhub\models\Setting;

class DashboardController extends Controller
{

    public function init()
    {
        $this->appendPageTitle(\Yii::t('DashboardModule.base', 'Dashboard'));
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
                'guestAllowedActions' => [
                    'index',
                    'stream'
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'stream' => [
                'class' => \humhub\modules\dashboard\components\actions\DashboardStream::className()
            ]
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
            return $this->render('index', array(
                'showProfilePostForm' => Yii::$app->getModule('dashboard')->settings->get('showProfilePostForm')
            ));
        }
    }

    /*
     * Update user settings for hiding share panel on dashboard
     */
    public function actionHidePanel()
    {
        // set tour status to seen for current user
        return Yii::$app->getModule('dashboard')->settings->user()->set('hideSharePanel', 1);
    }
}
