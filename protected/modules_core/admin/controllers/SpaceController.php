<?php

/**
 * @package humhub.modules_core.admin.controllers
 * @since 0.5
 */
class SpaceController extends Controller {

    public $subLayout = "/_layout";

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
            array('allow',
                'expression' => 'Yii::app()->user->isAdmin()'
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Returns a List of Users
     */
    public function actionIndex() {

        $model = new Space('search');

        if (isset($_GET['Space']))
            $model->attributes = $_GET['Space'];


        $this->render('index', array(
            'model' => $model
        ));
    }


}