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
        $lastEntryId = (int) Yii::app()->request->getParam('from');

        // create database query
        $criteria = new CDbCriteria();
        if ($lastEntryId > 0) {
            // start from last entry id loaded
            $criteria->condition = 'id<:lastEntryId';
            $criteria->params = array(':lastEntryId' => $lastEntryId);
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

    /**
     * Returns new notifications 
     */
    public function actionGetUpdateJson()
    {
        print self::getUpdateJson();
        Yii::app()->end();
    }

    /**
     * Returns a JSON which contains 
     * - Number of new / unread notification
     * - Notification Output for new HTML5 Notifications
     * 
     * @return string JSON String
     */
    public static function getUpdateJson()
    {
        $user = Yii::app()->user->getModel();

        $criteria = new CDbCriteria();
        $criteria->condition = 'user_id = :user_id';
        $criteria->addCondition('seen != 1');
        $criteria->params = array('user_id' => $user->id);

        $json['newNotifications'] = Notification::model()->count($criteria);

        $json['notifications'] = array();
        $criteria->addCondition('desktop_notified = 0');
        $notifications = Notification::model()->findAll($criteria);
        foreach ($notifications as $notification) {
            if ($user->getSetting("enable_html5_desktop_notifications", 'core', HSetting::Get('enable_html5_desktop_notifications', 'notification'))) {
                $json['notifications'][] = $notification->getTextOut();
            }
            $notification->desktop_notified = 1;
            $notification->update();
        }

        return CJSON::encode($json);
    }

}
