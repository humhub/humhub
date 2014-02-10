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
        $workspace = $this->getSpace();

        if (!$workspace->isMember()) {
            throw new CHttpException(401, 'Access denied!');
        }

        $json = array();
        $json['errorMessage'] = "None";

        $title = Yii::app()->request->getParam('todo', ""); // content of post
        $maxUsers = (int) Yii::app()->request->getParam('max_user', 1); // content of post
        $deathline = Yii::app()->request->getParam('deathline', ""); // content of post
        $preAssignedUsers = Yii::app()->request->getParam('preAssignedUsers', "");
        $fileList = Yii::app()->request->getParam('fileList', ""); // comma separted list of file guids
        // Tasks
        $task = new Task();
        $task->contentMeta->space_id = $workspace->id;
        $task->contentMeta->visibility = 0;

        $task->title = CHtml::encode(trim($title));
        $task->max_users = $maxUsers;
        $task->status = Task::STATUS_OPEN;

        if (Yii::app()->request->getParam('public', 0) == 1 && $workspace->canShare()) {
            $task->contentMeta->visibility = Content::VISIBILITY_PUBLIC;
        }

        if ($deathline != "")
            $task->deathline = CHtml::encode($deathline);

        if ($task->save()) {

            $wallEntry = $task->contentMeta->addToWall($workspace->wall_id);

            // Try to preassign users to this task
            $guids = explode(",", $preAssignedUsers);
            foreach ($guids as $guid) {
                $guid = trim($guid);
                $user = User::model()->findByAttributes(array('guid' => $guid));
                if ($user != null) {
                    $task->assignUser($user);
                }
            }

            File::attachToContent($task, Yii::app()->request->getParam('fileList', ""));

            // Build JSON Out
            $json['success'] = true;
            $json['wallEntryId'] = $wallEntry->id;
        } else {
            $json['success'] = false;
            $json['error'] = print_r($task->getErrors(), 1);
        }

        // returns JSON
        echo CJSON::encode($json);
        Yii::app()->end();
    }

    public function actionAssign() {

        $workspace = $this->getSpace();

        $taskId = Yii::app()->request->getParam('taskId');
        $task = Task::model()->findByPk($taskId);

        if ($task->contentMeta->canRead()) {
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

        if ($task->contentMeta->canRead()) {
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


        if ($task->contentMeta->canRead()) {
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

        if ($task->contentMeta->canRead()) {

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
        $json['wallEntryId'] = $task->contentMeta->getFirstWallEntryId(); // there should be only one
        echo CJSON::encode($json);
        Yii::app()->end();
    }

}
