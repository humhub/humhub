<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\controllers;

use Yii;
use humhub\components\Controller;
use humhub\modules\notification\models\Notification;
use humhub\modules\notification\models\forms\FilterForm;

/**
 * ListController
 *
 * @package humhub.modules_core.notification.controllers
 * @since 0.5
 */
class OverviewController extends Controller
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
     * Returns a List of all notifications for an user
     */
    public function actionIndex()
    {
        $pageSize = 10;
        $session = Yii::$app->session;
        $filterForm;    
        
        if($session->has('notification_overview_filter')) {
            $filterForm = $session->get('notification_overview_filter');
        } else {
            $filterForm = new FilterForm();
            $filterForm->initFilter();
        }
        
        //Fill filter and set session when post
        if(Yii::$app->request->post()) {
            $filterForm->load(Yii::$app->request->post());
            $session->set('notification_overview_filter', $filterForm);
        }
        
        $query = Notification::findByUser(Yii::$app->user->id);
        
        if($filterForm->classFilter != null && in_array('other', $filterForm->classFilter)) {
            $query->andFilterWhere(['not in' ,'class' , $filterForm->getExcludeFilter()]);
        } else if($filterForm->classFilter != null){
            $query->andFilterWhere(['in' ,'class' , $filterForm->classFilter]);
        } else {
            return $this->render('index',[ 
                    'notificationEntries' => [],
                    'filterForm' => $filterForm,
                    'pagination' => null
            ]);
        }

        $countQuery = clone $query;
        $pagination = new \yii\data\Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $pageSize]);
        
        //Reset pagegination after new filter set
        if(Yii::$app->request->post()) {
            $pagination->setPage(0);
        }
        
        $query->offset($pagination->offset)->limit($pagination->limit);
        
        return $this->render('index', array(
                    'notificationEntries' => $query->all(),
                    'filterForm' => $filterForm,
                    'pagination' => $pagination
        ));
    }
}
