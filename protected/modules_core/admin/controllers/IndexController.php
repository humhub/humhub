<?php

/**
 * @package humhub.modules_core.admin.controllers
 * @since 0.5
 */
class IndexController extends Controller {

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
     * List all available user groups
     */
    public function actionIndex() {
        $this->redirect(Yii::app()->createUrl('//admin/setting'));

    }

}