<?php

/**
 * @package humhub.modules_core.admin.controllers
 * @since 0.5
 */
class ModuleController extends Controller {

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

    public function actionIndex() {
        ModuleManager::flushCache();
        $this->render('index', array());
    }

    public function actionEnable() {

        $moduleId = Yii::app()->request->getQuery('moduleId');

        $definition = Yii::app()->moduleManager->getDefinition($moduleId);
        if ($definition == null) {
            throw new CHttpException(500, Yii::t('AdminModule.base', 'Could not find requested module!'));
        }

        if (!Yii::app()->moduleManager->isEnabled($moduleId)) {
            Yii::app()->moduleManager->enable($moduleId);
        }

        $this->redirect(Yii::app()->createUrl('admin/module'));
    }

    /**
     *
     * @throws CHttpException
     */
    public function actionDisable() {

        $moduleId = Yii::app()->request->getQuery('moduleId');

        $definition = Yii::app()->moduleManager->getDefinition($moduleId);
        if ($definition == null) {
            throw new CHttpException(500, Yii::t('AdminModule.base', 'Could not find requested module!'));
        }

        if (Yii::app()->moduleManager->isEnabled($moduleId)) {
            Yii::app()->moduleManager->disable($moduleId);
        }

        $this->redirect(Yii::app()->createUrl('admin/module'));
    }

}
