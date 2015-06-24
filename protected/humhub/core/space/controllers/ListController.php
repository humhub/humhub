<?php

/**
 * ListController
 *
 * @author Luke
 * @package humhub.modules_core.space.controllers
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

    public function actionIndex()
    {

        $criteria = new CDbCriteria();
        if (HSetting::Get('spaceOrder', 'space') == 0) {
            $criteria->order = 'name ASC';
        } else {
            $criteria->order = 'last_visit DESC';
        }

        $memberships = SpaceMembership::model()->with('space')->findAllByAttributes(array(
            'user_id' => Yii::app()->user->id,
            'status' => SpaceMembership::STATUS_MEMBER,
                ), $criteria);

        $this->renderPartial('index', array('memberships' => $memberships), false, true);
    }

}
