<?php

/**
 * CreateController is responsible for creation of new spaces
 *
 * @author Luke
 * @package humhub.modules_core.space.controllers
 * @since 0.5
 */
class CreateController extends Controller {

    public function actionIndex() {
        $this->redirect($this->createUrl('create/create'));
    }

    /**
     * Creates a new Space
     *
     */
    public function actionCreate() {

        $model = new SpaceCreateForm;

        // Ajax Validation
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'space-create-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        if (isset($_POST['SpaceCreateForm'])) {
            $_POST['SpaceCreateForm'] = Yii::app()->input->stripClean($_POST['SpaceCreateForm']);

            $model->attributes = $_POST['SpaceCreateForm'];

            if ($model->validate()) {

                $space = new Space();
                $space->name = $model->title;
                $space->description = $model->description;
                $space->join_policy = $model->join_policy;
                $space->visibility = $model->visibility;
                $space->save();

                // Save in this user variable, that the workspace was new created
                Yii::app()->user->setState('ws', 'created');

                // Redirect to the new created Space
                $this->htmlRedirect($this->createUrl('//space/space', array('sguid' => $space->guid)));
            }
        }

        $output = $this->renderPartial('create', array('model' => $model));
        Yii::app()->clientScript->render($output);
        echo $output;
        Yii::app()->end();
    }

}

?>
