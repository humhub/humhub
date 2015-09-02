<?php

/**
 * EntryController
 *
 * @package humhub.modules_core.notification.controllers
 * @since 0.5
 */
class EntryController extends Controller {

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

        $notificationId = (int) Yii::app()->request->getParam('id');

        $notification = Notification::model()->findByAttributes(array('user_id' => Yii::app()->user->id, 'id' => $notificationId));

        if ($notification == null)
            throw new CHttpException(500, 'Invalid notification id!');

        
        if ($notification->class != "SpaceApprovalRequestNotification" && $notification->class != "SpaceInviteNotification") {
            $notification->seen = 1;
            $notification->save();

            // Mark similar notification as read
            $notifications = Notification::model()->findAllByAttributes(array('target_object_model' => $notification->target_object_model, 'target_object_id' => $notification->target_object_id, 'user_id' => Yii::app()->user->id), 'seen != 1');
            foreach ($notifications as $n) {
                $n->markAsSeen();
            }
        }
        
        $notification->redirectToTarget();
    }

}