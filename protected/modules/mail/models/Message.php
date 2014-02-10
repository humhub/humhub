<?php

/**
 * This is the model class for table "message".
 *
 * The followings are the available columns in table 'message':
 * @property integer $id
 * @property string $title
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 * The followings are the available model relations:
 * @property MessageEntry[] $messageEntries
 * @property User[] $users
 *
 * @package humhub.modules.mail.models
 * @since 0.5
 */
class Message extends HActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Message the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'message';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('created_by, updated_by', 'numerical', 'integerOnly' => true),
            array('title', 'length', 'max' => 255),
            array('created_at, updated_at', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, title, created_at, created_by, updated_at, updated_by', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'entries' => array(self::HAS_MANY, 'MessageEntry', 'message_id', 'order' => 'created_at ASC'),
            'users' => array(self::MANY_MANY, 'User', 'user_message(message_id, user_id)'),
            'originator' => array(self::BELONGS_TO, 'User', 'created_by'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'title' => 'Title',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('title', $this->title, true);
        $criteria->compare('created_at', $this->created_at, true);
        $criteria->compare('created_by', $this->created_by);
        $criteria->compare('updated_at', $this->updated_at, true);
        $criteria->compare('updated_by', $this->updated_by);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * Returns the last message of this conversation
     */
    public function getLastEntry() {

        $criteria = new CDbCriteria;
        $criteria->limit = 1;
        $criteria->order = "created_at DESC";
        $criteria->condition = "message_id=" . $this->id;

        return MessageEntry::model()->find($criteria);
    }

    /**
     * Deletes a message, including all dependencies.
     */
    public function delete() {

        foreach (MessageEntry::model()->findAllByAttributes(array('message_id' => $this->id)) as $messageEntry) {
            $messageEntry->delete();
        }

        foreach (UserMessage::model()->findAllByAttributes(array('message_id' => $this->id)) as $userMessage) {
            $userMessage->delete();
        }

        parent::delete();
    }

    /**
     * Notify given user, about this message
     * An email will sent.
     */
    public function notify($user) {

        // User dont wants any emails
        if ($user->receive_email_messaging == User::RECEIVE_EMAIL_NEVER) {
            return;
        }

        $originatorName = $this->originator->displayName;
        $originatorGuid = $this->originator->guid;

        $andAddon = "";
        if (count($this->users) > 2) {
            $counter = count($this->users) - 1;
            $andAddon = Yii::t('MailModule.base', "and {counter} other users", array("{counter}" => $counter));
        }

        $message = new HMailMessage();
        $message->view = 'application.modules.mail.views.emails.NewMessage';
        $message->addFrom(HSetting::Get('systemEmailAddress', 'mailing'), HSetting::Get('systemEmailName', 'mailing'));
        $message->addTo($user->email);
        $message->subject = Yii::t('MailModule.base', 'New message from {senderName}', array("{senderName}" => $originatorName));
        $message->setBody(array(
            'message' => $this,
            'originatorName' => $originatorName,
            'originator' => $this->originator,
            'andAddon' => $andAddon,
            'entry' => $this->getLastEntry(),
            'user' => $user,
            'originatorGuid' => $originatorGuid,
                ), 'text/html');


        Yii::app()->mail->send($message);
    }

}