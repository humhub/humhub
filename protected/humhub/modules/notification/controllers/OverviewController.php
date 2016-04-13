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
     * Returns a List of all notifications for the session user
     */
    public function actionIndex()
    {
        $pageSize = 10;
        $session = Yii::$app->session;
        
        $filterForm = new FilterForm();
        $filterForm->initFilter();
        $filterForm->load(Yii::$app->request->get());
        
        $query = Notification::findByUser(Yii::$app->user->id);
        
        if($filterForm->isExcludeFilter()) {
            $query->andFilterWhere(['not in' ,'class' , $filterForm->getExcludeClassFilter()]);
        } else if($filterForm->isActive()){
            $query->andFilterWhere(['in' ,'class' , $filterForm->getIncludeClassFilter()]);
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
