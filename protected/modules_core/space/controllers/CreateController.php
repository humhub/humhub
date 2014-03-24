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
                $space->join_policy = 2;
                $space->visibility = 2;
                $space->save();

                // Add Membership
                $membership = new SpaceMembership;
                $membership->space_id = $space->id;
                $membership->user_id = Yii::app()->user->id;
                $membership->status = SpaceMembership::STATUS_MEMBER;
                $membership->invite_role = 1;
                $membership->admin_role = 1;
                $membership->share_role = 1;
                $membership->save();

                // Save in this user variable, that the workspace was new created
                Yii::app()->user->setState('ws', 'created');

                // Redirect to the new created Space
                $this->htmlRedirect($this->createUrl('//space/admin/edit', array('sguid' => $space->guid)));
            }
        }

        $output = $this->renderPartial('create', array('model' => $model));
        Yii::app()->clientScript->render($output);
        echo $output;
        Yii::app()->end();
    }

}

?>
