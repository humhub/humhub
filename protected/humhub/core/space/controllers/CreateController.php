<?php

/**
 * CreateController is responsible for creation of new spaces
 *
 * @author Luke
 * @package humhub.modules_core.space.controllers
 * @since 0.5
 */
class CreateController extends Controller
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
        $this->redirect($this->createUrl('create/create'));
    }

    /**
     * Creates a new Space
     */
    public function actionCreate()
    {

        if (!Yii::app()->user->canCreateSpace()) {
            throw new CHttpException(400, 'You are not allowed to create spaces!');
        }

        $model = new Space('edit');

        // Ajax Validation
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'space-create-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        if (isset($_POST['Space'])) {
            $_POST['Space'] = Yii::app()->input->stripClean($_POST['Space']);

            $model->attributes = $_POST['Space'];

            if ($model->validate() && $model->save()) {

                // Save in this user variable, that the workspace was new created
                Yii::app()->user->setState('ws', 'created');

                // Redirect to the new created Space
                $this->htmlRedirect($model->getUrl());
            }
        }

        $this->renderPartial('create', array('model' => $model), false, true);
    }

}

?>
