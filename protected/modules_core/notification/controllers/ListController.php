<?php

/**
 * ListController
 *
 * @package humhub.modules_core.notification.controllers
 * @since 0.5
 */
class ListController extends Controller {

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
     * Returns a List of all Comments belong to this Model
     */
    public function actionIndex() {

        $criteria = new CDbCriteria();
        $criteria->order = 'seen ASC, created_at DESC';
        $criteria->limit = 6;

        $notifications = Notification::model()->findAllByAttributes(array('user_id' => Yii::app()->user->id), $criteria);

        $this->renderPartial('index', array(
            'notifications' => $notifications
        ));
    }

}