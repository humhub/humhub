<?php

class ConfigController extends Controller {

    public $subLayout = "application.modules_core.admin.views._layout";

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
                'expression' => 'Yii::app()->user->isAdmin()',
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Configuration Action for Super Admins
     */
    public function actionIndex() {

        Yii::import('yiigii.forms.*');

        $form = new ConfigureForm;

        // uncomment the following code to enable ajax-based validation
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'configure-form') {
            echo CActiveForm::validate($form);
            Yii::app()->end();
        }

        if (isset($_POST['ConfigureForm'])) {
            $_POST['ConfigureForm'] = Yii::app()->input->stripClean($_POST['ConfigureForm']);
            $form->attributes = $_POST['ConfigureForm'];

            if ($form->validate()) {

                $form->password = HSetting::Set('password', $form->password, 'yiigii');
                $form->ipFilters = HSetting::Set('ipFilters', $form->ipFilters, 'yiigii');

                $this->redirect(Yii::app()->createUrl('yiigii/config/index'));
            }
        } else {

            $form->password = HSetting::Get('password', 'yiigii');
            $form->ipFilters = HSetting::Get('ipFilters', 'yiigii');
        }

        $this->render('index', array('model' => $form));
    }

}

?>
