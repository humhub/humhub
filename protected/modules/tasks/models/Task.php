<?php

/**
 * This is the model class for table "task".
 *
 * The followings are the available columns in table 'task':
 * @property integer $id
 * @property string $title
 * @property string $deathline
 * @property integer $max_users
 * @property integer $min_users
 * @property integer $precent
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 */
class Task extends HActiveRecordContent
{

    public $preassignedUsers;
    public $userToNotify = "";

    // Status
    const STATUS_OPEN = 1;
    const STATUS_FINISHED = 5;

    public $autoAddToWall = true;

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Task the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'task';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('title,  created_at, created_by, updated_at, updated_by', 'required'),
            array('max_users, percent, created_by, updated_by', 'numerical', 'integerOnly' => true),
            array('preassignedUsers, deathline, max_users, min_users', 'safe'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'users' => array(self::HAS_MANY, 'TaskUser', 'task_id'),
            'creator' => array(self::BELONGS_TO, 'User', 'created_by'),
        );
    }

    /**
     * Deletes a Task including its dependencies.
     */
    public function delete()
    {

        // delete all tasks user assignments
        $taskUser = TaskUser::model()->findAllByAttributes(array('task_id' => $this->id));
        foreach ($taskUser as $tu) {
            $tu->delete();
        }

        Notification::remove('Task', $this->id);

        return parent::delete();
    }

    /**
     * Returns the Wall Output
     */
    public function getWallOut()
    {
        return Yii::app()->getController()->widget('application.modules.tasks.widgets.TaskWallEntryWidget', array('task' => $this), true);
    }

    /**
     * Before Save Addons
     *
     * @return type
     */
    public function afterSave()
    {

        parent::afterSave();

        if ($this->isNewRecord) {
            $activity = Activity::CreateForContent($this);
            $activity->type = "TaskCreated";
            $activity->module = "tasks";
            $activity->save();
            $activity->fire();

            // Attach Preassigned Users
            $guids = explode(",", $this->preassignedUsers);
            foreach ($guids as $guid) {
                $guid = trim($guid);
                $user = User::model()->findByAttributes(array('guid' => $guid));
                if ($user != null) {
                    $this->assignUser($user);
                }
            }

            // notify assigned Users
            if ($this->userToNotify != "") {
                $guids_nofify = explode(",", $this->userToNotify);
                foreach ($guids_nofify as $guid_notify) {
                    $guid_notify = trim($guid_notify);
                    $user = User::model()->findByAttributes(array('guid' => $guid_notify));
                    if ($user != null) {
                        $this->notifyUser($user);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Returns assigned users to this task
     */
    public function getAssignedUsers()
    {
        $users = array();
        $tus = TaskUser::model()->findAllByAttributes(array('task_id' => $this->id));
        foreach ($tus as $tu) {
            $user = User::model()->findByPk($tu->user_id);
            if ($user != null)
                $users[] = $user;
        }
        return $users;
    }

    /**
     * Assign user to this task
     */
    public function assignUser($user = "")
    {

        if ($user == "")
            $user = Yii::app()->user->getModel();

        $au = TaskUser::model()->findByAttributes(array('task_id' => $this->id, 'user_id' => $user->id));
        if ($au == null) {

            $au = new TaskUser;
            $au->task_id = $this->id;
            $au->user_id = $user->id;
            $au->save();

            # Handled by Notification now
            #$activity = Activity::CreateForContent($this);
            #$activity->type = "TaskAssigned";
            #$activity->module = "tasks";
            #$activity->content->user_id = $user->id;
            #$activity->save();
            #$activity->fire();
            // Fire Notification to creator
            $notification = new Notification();
            $notification->class = "TaskAssignedNotification";
            $notification->user_id = $au->user_id; // Assigned User
            $notification->space_id = $this->content->space_id;
            $notification->source_object_model = 'Task';
            $notification->source_object_id = $this->id;
            $notification->target_object_model = 'Task';
            $notification->target_object_id = $this->id;
            $notification->save();

            return true;
        }
        return false;
    }

    /**
     * UnAssign user to this task
     */
    public function unassignUser($user = "")
    {
        if ($user == "")
            $user = Yii::app()->user->getModel();

        $au = TaskUser::model()->findByAttributes(array('task_id' => $this->id, 'user_id' => $user->id));
        if ($au != null) {
            $au->delete();

            // Delete Activity for Task Assigned
            $activity = Activity::model()->findByAttributes(array(
                'type' => 'TaskAssigned',
                'object_model' => "Task",
                'user_id' => $user->id,
                'object_id' => $this->id
            ));
            if ($activity)
                $activity->delete();

            // Try to delete TaskAssignedNotification if exists
            foreach (Notification::model()->findAllByAttributes(array('class' => 'TaskAssignedNotification', 'target_object_model' => 'Task', 'target_object_id' => $this->id)) as $notification) {
                $notification->delete();
            }

            return true;
        }
        return false;
    }

    public function changePercent($newPercent)
    {

        if ($this->percent != $newPercent) {
            $this->percent = $newPercent;
            $this->save();
        }

        if ($newPercent == 100) {
            $this->changeStatus(Task::STATUS_FINISHED);
        }

        if ($this->percent != 100 && $this->status == Task::STATUS_FINISHED) {
            $this->changeStatus(Task::STATUS_OPEN);
        }

        return true;
    }

    public function changeStatus($newStatus)
    {

        $this->status = $newStatus;
        $this->save();

        // Try to delete Old Finished Activity Activity
        $activity = Activity::model()->findByAttributes(array(
            'type' => 'TaskFinished',
            'module' => 'tasks',
            'object_model' => "Task",
            'object_id' => $this->id
        ));
        if ($activity)
            $activity->delete();


        if ($newStatus == Task::STATUS_FINISHED) {

            // Fire Activity for that
            $activity = Activity::CreateForContent($this);
            $activity->type = "TaskFinished";
            $activity->module = "tasks";
            $activity->content->user_id = Yii::app()->user->id;
            $activity->save();
            $activity->fire();

            // Fire Notification to creator
            if ($this->created_by != Yii::app()->user->id) {
                $notification = new Notification();
                $notification->class = "TaskFinishedNotification";
                $notification->user_id = $this->created_by; // To Creator
                $notification->space_id = $this->content->space_id;
                $notification->source_object_model = 'Task';
                $notification->source_object_id = $this->id;
                $notification->target_object_model = 'Task';
                $notification->target_object_id = $this->id;
                $notification->save();
            }

            # Causes Double Usage of Task
            #if ($this->percent != 100) {
            #    $this->changePercent(100);
            #}
        } else {

            // Try to delete TaskFinishedNotification if exists
            foreach (Notification::model()->findAllByAttributes(array('class' => 'TaskFinishedNotification', 'target_object_model' => 'Task', 'target_object_id' => $this->id)) as $notification) {
                $notification->delete();
            }


            // Reset Percentage
            #if ($this->percent == 100) {
            #    $this->changePercent(0);
            #}
        }

        return true;
    }

    public static function GetUsersOpenTasks()
    {

        $sql = " SELECT task.* FROM task_user " .
                " LEFT JOIN task ON task.id = task_user.task_id " .
                " WHERE task_user.user_id=:userId AND task.status=:status";

        $params = array();
        $params[':userId'] = Yii::app()->user->id;
        $params[':status'] = Task::STATUS_OPEN;

        $tasks = Task::model()->findAllBySql($sql, $params);

        return $tasks;
    }

    /**
     * Returns a title/text which identifies this IContent.
     *
     * e.g. Task: foo bar 123...
     *
     * @return String
     */
    public function getContentTitle()
    {
        return "\"" . Helpers::truncateText($this->title, 25) . "\"";
    }

    /**
     * Assign user to this task
     */
    public function notifyUser($user = "")
    {

        if ($user == "") {
            $user = Yii::app()->user->getModel();
        }

        // Fire Notification to user
        $notification = new Notification();
        $notification->class = "TaskCreatedNotification";
        $notification->user_id = $user->id; // Assigned User
        $notification->space_id = $this->content->space_id;
        $notification->source_object_model = 'Task';
        $notification->source_object_id = $this->id;
        $notification->target_object_model = 'Task';
        $notification->target_object_id = $this->id;
        $notification->save();
    }

}
