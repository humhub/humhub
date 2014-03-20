<?php

/**
 * This is the model class for table "notification".
 *
 * The followings are the available columns in table 'notification':
 * @property integer $id
 * @property string $class
 * @property integer $user_id
 * @property integer $seen
 * @property integer $emailed
 * @property string $source_object_model
 * @property integer $source_object_id
 * @property string $target_object_model
 * @property integer $target_object_id
 * @property integer $space_id
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 * @package humhub.modules_core.notification.models
 * @since 0.5
 */
class Notification extends HActiveRecord {

    /**
     * Normally a notification is set to seen, after we clicked at it.
     * In special cases it´s may be needed to mark an notification as seen after
     * we displayed it one time. (Set to: true)
     *
     * @var type
     */
    public $seenWithoutClick = false;
    public $webView = "comment.views.notifications.newComment";
    public $mailView = "application.module.comment.views.notifications.newComment_mail";

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Notification the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'notification';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('class, user_id, created_at, created_by, updated_at, updated_by', 'required'),
            array('user_id, seen, source_object_id, target_object_id, emailed, space_id, created_by, updated_by', 'numerical', 'integerOnly' => true),
            array('class, source_object_model, target_object_model', 'length', 'max' => 100),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, class, user_id, seen, source_object_model, source_object_id, target_object_model, target_object_id, space_id, created_at, created_by, updated_at, updated_by', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'space' => array(self::BELONGS_TO, 'Space', 'space_id'),
            'user' => array(self::BELONGS_TO, 'User', 'user_id'),
        );
    }

    protected function instantiate($attributes) {

        $className = $attributes['class'];
        // Instanciate correct Asset Model
        $model = new $className(null);
        return $model;
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'class' => 'Class',
            'user_id' => 'User',
            'seen' => 'Seen',
            'source_object_model' => 'Source Object Model',
            'source_object_id' => 'Source Object',
            'target_object_model' => 'Target Object Model',
            'target_object_id' => 'Target Object',
            'space_id' => 'Space',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        );
    }

    protected function beforeSave() {

        if ($this->isNewRecord) {

            if ($this->seen == "") {
                $this->seen = 0;
            }
        }

        return parent::beforeSave();
    }

    public static function remove($model, $id) {
        $notifications = Notification::model()->findAllByAttributes(array('target_object_model' => $model, 'target_object_id' => $id));
        foreach ($notifications as $notification) {
            $notification->delete();
        }
        $notifications = Notification::model()->findAllByAttributes(array('source_object_model' => $model, 'source_object_id' => $id));
        foreach ($notifications as $notification) {
            $notification->delete();
        }
    }

    /*
      public static function removeTargetModel($name, $pk) {
      $notifications = Notification::model()->findAllByAttributes(array('target_object_model'=>$name, 'target_object_id'=>$pk));
      foreach ($notifications as $notification) {
      $notification->delete();
      }
      }


     */

    public function getSourceObject() {

        $model = $this->source_object_model;
        $pk = $this->source_object_id;

        if ($model == "" || $pk == "")
            return null;

        return $model::model()->findByPk($pk);
    }

    public function getTargetObject() {

        $model = $this->target_object_model;
        $pk = $this->target_object_id;

        if ($model == "" || $pk == "")
            return null;

        return $model::model()->findByPk($pk);
    }

    public function getUrl() {
        return Yii::app()->createUrl("//notification/entry", array('id' => $this->id));
    }

    /**
     * After clicking on a notification
     *
     * This function only works when the Source Object has Behavior
     * HContentBehavior or HContentAddonBehavior
     *
     * If not, overwrite this function
     */
    public function redirectToTarget() {

        // Can be a comment, like, post, ...
        $sourceObj = $this->getSourceObject();
        if ($sourceObj == null) {
            die("Could not load Source Obj");
        }

        // Find Object to Redirect to
        $contentObj = null;
        if ($sourceObj instanceof HActiveRecordContent) {
            $contentObj = $sourceObj;
        } elseif ($sourceObj->asa('HContentAddonBehavior') !== null) {
            $contentObj = $sourceObj->getContentObject();
        } else {
            throw new CHttpException(500, 'Invalid source object');
        }

        $objectModel = get_class($contentObj);
        $objectId = $contentObj->id;

        Yii::app()->getController()->redirect(Yii::app()->getController()->createUrl(
                        "//wall/perma/content", array(
                    'model' => $objectModel,
                    'id' => $objectId,
                        )
        ));
    }

    /**
     * Generates Mail Output for this notification
     *
     * @return type
     */
    public function getMailOut() {
        $controller = new CController('MailX');
        $viewPath = Yii::getPathOfAlias($this->mailView) . '.php';

        if ($viewPath == ".php")
            return "not found: " . $this->mailView;

        return $controller->renderInternal($viewPath, array(
                    'notification' => $this,
                    'sourceObject' => $this->getSourceObject(),
                    'targetObject' => $this->getTargetObject(),
                    'creator' => $this->getCreator(),
                        ), true
        );
    }

    public function markAsSeen() {
        $this->seen = 1;
        $this->save();
    }

    /**
     * Generates Output for this notification
     *
     * @return type
     */
    public function getOut() {

        $out = Yii::app()->getController()->renderPartial(
                $this->webView, array(
            'notification' => $this,
            'sourceObject' => $this->getSourceObject(),
            'targetObject' => $this->getTargetObject(),
            'creator' => $this->getCreator(),
                ), true
        );

        // Mark as seen, after we rendered it. (New Ribbon)
        if ($this->seenWithoutClick) {
            $this->markAsSeen();
        }

        return $out;
    }

}