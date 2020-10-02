<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dashboard\controllers;

use humhub\components\behaviors\AccessControl;
use humhub\components\Controller;
use humhub\modules\dashboard\components\actions\DashboardStreamAction;
use humhub\modules\ui\view\components\View;
use Yii;

class DashboardController extends Controller
{
    /**
     * View context used for the dashboard view
     * @see View::$viewContext
     */
    const VIEW_CONTEXT = 'dashboard';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->appendPageTitle(Yii::t('DashboardModule.base', 'Dashboard'));
        $this->view->setViewContext(static::VIEW_CONTEXT);
        return parent::init();
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => AccessControl::class,
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
                'class' => DashboardStreamAction::class,
                'activity' => false
            ],
            'activity-stream' => [
                'class' => DashboardStreamAction::class,
                'activity' => true
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
            return $this->render('index_guest', []);
        } else {
            return $this->render('index', [
                'showProfilePostForm' => Yii::$app->getModule('dashboard')->settings->get('showProfilePostForm'),
                'contentContainer' => Yii::$app->user->getIdentity()
            ]);
        }
    }

}
