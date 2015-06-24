<?php

/**
 * This is the model class for table "user_mentioning".
 *
 * The followings are the available columns in table 'user_mentioning':
 * @property integer $id
 * @property string $object_model
 * @property integer $object_id
 * @property integer $user_id
 */
class UserMentioning extends HActiveRecord
{

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return UserMentioning the static model class
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
        return 'user_mentioning';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('object_model, object_id, user_id', 'required'),
            array('object_id, user_id', 'numerical', 'integerOnly' => true),
            array('object_model', 'length', 'max' => 100),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, object_model, object_id, user_id', 'safe', 'on' => 'search'),
        );
    }

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
                'mustBeInstanceOf' => array('HActiveRecordContent', 'HActiveRecordContentAddon'),
            ),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'user' => array(self::BELONGS_TO, 'User', 'user_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'object_model' => 'Object Model',
            'object_id' => 'Object',
            'user_id' => 'User',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('object_model', $this->object_model, true);
        $criteria->compare('object_id', $this->object_id);
        $criteria->compare('user_id', $this->user_id);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    public function afterSave()
    {
        $this->sendNotification();
        return parent::afterSave();
    }

    /**
     * Sends an notification about new mentioning
     */
    protected function sendNotification()
    {
        $record = $this->getUnderlyingObject();

        // Avoid notifications when user mentioned himself
        if ($record instanceof HActiveRecordContent && $this->user_id == $record->content->user_id) {
            return;
        } elseif ($record instanceof HActiveRecordContentAddon && $this->user_id == $record->created_by) {
            return;
        }
        $content = $record->content;

        // Check if user has read access to this content
        if ($content->canRead($this->user_id)) {

            // Mentioned users automatically follows the content
            $content->getUnderlyingObject()->follow($this->user_id);

            // Fire Notification to user
            $notification = new Notification();
            $notification->class = "MentionedNotification";
            $notification->user_id = $this->user_id;
            if (get_class($content->container) == 'Space') {
                $notification->space_id = $content->container->id;
            }
            $notification->source_object_model = $this->object_model;
            $notification->source_object_id = $this->object_id;
            $notification->target_object_model = $this->object_model;
            $notification->target_object_id = $this->object_id;
            $notification->save();
        }
    }

    /**
     * Parses a given text for mentioned users and creates an mentioning for them.
     * 
     * @param HActiveRecordContent|HActiveRecordContentAddon $record
     * @param string $text
     */
    public static function parse($record, $text)
    {

        if ($record instanceof HActiveRecordContent || $record instanceof HActiveRecordContentAddon) {
            preg_replace_callback('@\@\-u([\w\-]*?)($|\s|\.)@', function($hit) use(&$record) {
                $user = User::model()->findByAttributes(array('guid' => $hit[1]));
                if ($user !== null) {
                    // Check the user was already mentioned (e.g. edit)
                    $mention = UserMentioning::model()->findByAttributes(array('object_model' => get_class($record), 'object_id' => $record->getPrimaryKey(), 'user_id' => $user->id));
                    if ($mention === null) {
                        $mention = new UserMentioning();
                        $mention->object_model = get_class($record);
                        $mention->object_id = $record->getPrimaryKey();
                        $mention->user_id = $user->id;
                        $mention->save();
                        $mention->setUnderlyingObject($record);
                    }
                }
            }, $text);
        } else {
            throw new Exception("Mentioning can only used in HActiveRecordContent or HActiveRecordContentAddon objects!");
        }
    }

}
