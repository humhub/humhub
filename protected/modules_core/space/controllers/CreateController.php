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

            if (Yii::app()->user->canCreatePublicSpace()) {
                $model->visibility = Space::VISIBILITY_ALL;
            } else {
                $model->visibility = Space::VISIBILITY_NONE;
            }
            
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
