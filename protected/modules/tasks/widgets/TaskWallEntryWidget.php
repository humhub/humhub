<?php

/**
 * Shows a Task Wall Entry
 */
class TaskWallEntryWidget extends HWidget {

    public $task;

    public function run() {
        $user = $this->task->creator;

        $assignedUsers = $this->task->getAssignedUsers();
        $assignedToCurrentUser = false;

        $assetPrefix = Yii::app()->assetManager->publish(dirname(__FILE__) . '/../resources', true, 0, defined('YII_DEBUG'));
        Yii::app()->clientScript->registerCssFile($assetPrefix . '/tasks.css');

        // Check if current users is assigned to this task (faster way)
        /*
          foreach ($assignedUsers as $au) {
          if ($au->id == Yii::app()->user->id) {
          $assignedToCurrentUser=true;
          break;
          }
          }

          'assignedUsers' => $assignedUsers,
          'assignedToCurrentUser' => $assignedToCurrentUser

         */
        $this->render('entry', array(
            'task' => $this->task,
            'user' => $user,
            'space' => $this->task->content->container,
        ));
    }

}

?>