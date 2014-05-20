<?php

/**
 * ListController
 *
 * @package humhub.modules_core.notification.controllers
 * @since 0.5
 */
class ListController extends Controller
{

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
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
     * Returns a List of all notifications for an user
     */
    public function actionIndex()
    {

        // the id from the last entry loaded
        $lastEntryId = Yii::app()->request->getParam('from');

        // create database query
        $criteria = new CDbCriteria();
        if ($lastEntryId > 0) {
            // start from last entry id loaded
            $criteria->condition = 'id<' . $lastEntryId;
        }
        $criteria->order = 'seen ASC, created_at DESC';
        $criteria->limit = 6;

        // safe query
        $notifications = Notification::model()->findAllByAttributes(array('user_id' => Yii::app()->user->id), $criteria);

        // variable for notification list
        $output = "";

        foreach ($notifications as $notification) {
            // format and save all entries
            $output .= $notification->getOut();

            // get the id from the last entry
            $lastEntryId = $notification->id;
        }

        // build json array
        $json = array();
        $json['output'] = $output;
        $json['lastEntryId'] = $lastEntryId;
        $json['counter'] = count($notifications);

        // return json
        echo CJSON::encode($json);

        // compete action
        Yii::app()->end();

    }


    public function actionMarkAsSeen()
    {

        // build query
        $criteria = new CDbCriteria();
        $criteria->condition = 'seen=0';

        // load all unseen notification for this user
        $notifications = Notification::model()->findAllByAttributes(array('user_id' => Yii::app()->user->id), $criteria);

        foreach ($notifications as $notification) {
            // mark all unseen notification as seen
            $notification->markAsSeen();
        }

        // compete action
        Yii::app()->end();
    }

}