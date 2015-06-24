<?php

/**
 * @package humhub.modules_core.admin.controllers
 * @since 0.5
 */
class SpaceController extends Controller
{

    public $subLayout = "/_layout";

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
            array('allow',
                'expression' => 'Yii::app()->user->isAdmin()'
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Shows all available spaces
     */
    public function actionIndex()
    {

        $model = new Space('search');

        if (isset($_GET['Space']))
            $model->attributes = $_GET['Space'];


        $this->render('index', array(
            'model' => $model
        ));
    }

    /**
     * General Space Settings 
     */
    public function actionSettings()
    {
        $form = new SpaceSettingsForm;
        $form->defaultJoinPolicy = HSetting::Get('defaultJoinPolicy', 'space');
        $form->defaultVisibility = HSetting::Get('defaultVisibility', 'space');

        // uncomment the following code to enable ajax-based validation
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'space-settings-form') {
            echo CActiveForm::validate($form);
            Yii::app()->end();
        }

        if (isset($_POST['SpaceSettingsForm'])) {
            $_POST['SpaceSettingsForm'] = Yii::app()->input->stripClean($_POST['SpaceSettingsForm']);
            $form->attributes = $_POST['SpaceSettingsForm'];

            if ($form->validate()) {
                HSetting::Set('defaultJoinPolicy', $form->defaultJoinPolicy, 'space');
                HSetting::Set('defaultVisibility', $form->defaultVisibility, 'space');

                // set flash message
                Yii::app()->user->setFlash('data-saved', Yii::t('AdminModule.controllers_SpaceController', 'Saved'));
                $this->redirect($this->createUrl('settings'));
            }
        }

        $this->render('settings', array('model' => $form));
    }

}
