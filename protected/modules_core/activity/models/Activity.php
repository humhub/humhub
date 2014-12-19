<?php

/**
 * This is the model class for table "activity".
 *
 * The followings are the available columns in table 'activity':
 * @property integer $id
 * @property string $type
 * @property string $module
 * @property integer $object_id
 * @property string $object_model
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 * The followings are the available model relations:
 * @property User $user
 * @property Space $workspace
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.modules_core.activity.models
 * @since 0.5
 */
class Activity extends HActiveRecordContent
{

    public $autoAddToWall = false;

    /**
     * Add mix-ins to this model
     *
     * @return type
     */
    public function behaviors()
    {
        return array(
            'HUnderlyingObjectBehavior' => array(
                'class' => 'application.behaviors.HUnderlyingObjectBehavior',
                'mustBeInstanceOf' => array('HActiveRecordContent', 'HActiveRecordContentContainer', 'HActiveRecordContentAddon'),
            ),
        );
    }

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Activity the static model class
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
        return 'activity';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('created_by, updated_by', 'numerical', 'integerOnly' => true),
            array('type', 'length', 'max' => 45),
            array('activity_data, created_at, updated_at, visibility', 'safe'),
            array('object_model, object_id', 'safe'),
        );
    }

    /**
     * Creates an instance of Activity for given object.
     *
     * The activity instance inherits options/attributes like user, space or 
     * visibility of the given object.
     * 
     * Dont forget to also set activity attributes like type, module before
     * saving it!
     * 
     * @param Mixed $content Instance of HActiveRecordContent or HActiveRecordContentAddon
     * @return Activity Prepared activity for given $object
     */
    public static function CreateForContent($object)
    {

        $activity = new Activity;

        if (!$object instanceof HActiveRecordContent && !$object instanceof HActiveRecordContentAddon) {
            throw new CHttpException(500, Yii::t('ActivityModule.models_Activity', 'Could not create activity for this object type!'));
        }

        $content = $object->content;

        $activity->content->user_id = $content->created_by;

        // Always take visibilty of Content Object for that activity
        $activity->content->visibility = $content->visibility;

        // Auto Set object_model & object_id of given object
        $activity->object_model = get_class($object);
        $activity->object_id = $object->getPrimaryKey();

        // Also assign space_id if set
        if ($content->container instanceof Space) {
            $activity->content->space_id = $content->container->id;
        }

        return $activity;
    }

    /**
     * After Saving a new activity, the activity is automatically published
     * to the underlying workspace or user wall
     *
     */
    public function fire()
    {

        if ($this->content->container instanceof Space) {

            // Post this activity to space wall
            $this->content->addToWall($this->content->container->wall_id);
        } elseif ($this->content->container instanceof User) {

            // Post this activity to users wall
            if (isset(Yii::app()->user)) {
                $this->content->addToWall(Yii::app()->user->getModel()->wall_id);
            } else {
                // Maybe Console Script without Yii::app()->user
                $user = User::model()->findByPk($this->created_by);
                $this->content->addToWall($user->wall_id);
            }
        }
    }

    /**
     * Gets the Wall Output
     */
    public function getWallOut()
    {
        return Yii::app()->getController()->widget('application.modules_core.activity.widgets.ActivityWidget', array('activity' => $this), true);
    }

    /**
     * Returns Mail Output for that activity
     *
     * @return type
     */
    public function getMailOut()
    {
        $controller = new Controller('MailX');

        // Determine View
        $view = 'application.modules_core.activity.views.activities.' . $this->type . "_mail";

        if ($this->module != "") {
            $view = 'application.modules_core.' . $this->module . '.views.activities.' . $this->type . "_mail";
            $viewPath = Yii::getPathOfAlias($view) . '.php';

            // Seems not exists, try 3rd party module folder
            if (!file_exists($viewPath)) {
                $view = 'application.modules.' . $this->module . '.views.activities.' . $this->type . "_mail";
            }
        }

        $viewPath = Yii::getPathOfAlias($view) . '.php';

        $underlyingObject = $this->getUnderlyingObject();

        $workspace = null;
        if ($this->content->space_id != "") {
            $workspace = Space::model()->findByPk($this->content->space_id);
        }

        $user = $this->content->user;
        if ($user == null)
            return;

        return $controller->renderInternal($viewPath, array(
                    'activity' => $this,
                    'wallEntryId' => 0,
                    'user' => $user,
                    'target' => $underlyingObject,
                    'workspace' => $workspace
                        ), true
        );
    }

}
