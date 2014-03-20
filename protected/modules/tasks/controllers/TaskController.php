<?php

class TaskController extends Controller {

    public $subLayout = "application.modules_core.space.views.space._layout";

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
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'users' => array('@'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Actions
     *
     * @return type
     */
    public function actions() {
        return array(
            'stream' => array(
                'class' => 'application.modules.tasks.TasksStreamAction',
                'mode' => 'normal',
            ),
        );
    }

    /**
     * Add mix-ins to this model
     *
     * @return type
     */
    public function behaviors() {
        return array(
            'SpaceControllerBehavior' => array(
                'class' => 'application.modules_core.space.SpaceControllerBehavior',
            ),
        );
    }

    /**
     * Shows the Tasks tab
     */
    public function actionShow() {

        $workspace = $this->getSpace();
        $this->render('show', array('workspace' => $workspace));
    }

    /**
     * Posts a new tasks
     *
     * @return type
     */
    public function actionCreate() {

        $this->forcePostRequest();
        $_POST = Yii::app()->input->stripClean($_POST);

        $task = new Task();
        $task->content->populateByForm();
        $task->title = Yii::app()->request->getParam('title');
        $task->max_users = Yii::app()->request->getParam('max_users');
        $task->deathline = Yii::app()->request->getParam('deathline');
        $task->preassignedUsers = Yii::app()->request->getParam('preassignedUsers');
        $task->status = Task::STATUS_OPEN;

        if ($task->validate()) {
            $task->save();
            $this->renderJson(array('wallEntryId' => $task->content->getFirstWallEntryId()));
        } else {
            $this->renderJson(array('errors' => $task->getErrors()), false);
        }
    }

    public function actionAssign() {

        $workspace = $this->getSpace();

        $taskId = Yii::app()->request->getParam('taskId');
        $task = Task::model()->findByPk($taskId);

        if ($task->content->canRead()) {
            $task->assignUser();
            $this->printTask($task);
        } else {
            throw new CHttpException(401, 'Could not access task!');
        }
        Yii::app()->end();
    }

    public function actionUnAssign() {

        $workspace = $this->getSpace();

        $taskId = Yii::app()->request->getParam('taskId');
        $task = Task::model()->findByPk($taskId);

        if ($task->content->canRead()) {
            $task->unassignUser();
            $this->printTask($task);
        } else {
            throw new CHttpException(401, 'Could not access task!');
        }
        Yii::app()->end();
    }

    public function actionChangePercent() {

        $workspace = $this->getSpace();

        $taskId = (int) Yii::app()->request->getParam('taskId');
        $percent = (int) Yii::app()->request->getParam('percent');
        $task = Task::model()->findByPk($taskId);


        if ($task->content->canRead()) {
            $task->changePercent($percent);
            $this->printTask($task);
        } else {
            throw new CHttpException(401, Yii::t('TasksModule.base', 'Could not access task!'));
        }
        Yii::app()->end();
    }

    public function actionChangeStatus() {

        $space = $this->getSpace();

        $taskId = (int) Yii::app()->request->getParam('taskId');
        $status = (int) Yii::app()->request->getParam('status');
        $task = Task::model()->findByPk($taskId);

        if ($task->content->canRead()) {

            $task->changeStatus($status);
            $this->printTask($task);
        } else {
            throw new CHttpException(401, 'Could not access task!');
        }
        Yii::app()->end();
    }

    /**
     * Prints the given task wall output include the affected wall entry id
     *
     * @param Task $task
     */
    protected function printTask($task) {

        $output = $task->getWallOut();
        Yii::app()->clientScript->render($output);

        $json = array();
        $json['output'] = $output;
        $json['wallEntryId'] = $task->content->getFirstWallEntryId(); // there should be only one
        echo CJSON::encode($json);
        Yii::app()->end();
    }

}
