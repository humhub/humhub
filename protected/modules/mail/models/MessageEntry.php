<?php

/**
 * This is the model class for table "message_entry".
 *
 * The followings are the available columns in table 'message_entry':
 * @property integer $id
 * @property integer $message_id
 * @property integer $user_id
 * @property integer $file_id
 * @property string $content
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 * The followings are the available model relations:
 * @property Message $message
 * @property User $user
 * @property File $file
 *
 * @package humhub.modules.mail.models
 * @since 0.5
 */
class MessageEntry extends HActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return MessageEntry the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'message_entry';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('message_id, user_id, content', 'required'),
            array('message_id, user_id, file_id, created_by, updated_by', 'numerical', 'integerOnly' => true),
            array('created_at, updated_at', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, message_id, user_id, file_id, content, created_at, created_by, updated_at, updated_by', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'message' => array(self::BELONGS_TO, 'Message', 'message_id'),
            'user' => array(self::BELONGS_TO, 'User', 'user_id'),
            'file' => array(self::BELONGS_TO, 'File', 'file_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'message_id' => 'Message',
            'user_id' => 'User',
            'file_id' => 'File',
            'content' => 'Content',
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
        $criteria->compare('message_id', $this->message_id);
        $criteria->compare('user_id', $this->user_id);
        $criteria->compare('file_id', $this->file_id);
        $criteria->compare('content', $this->content, true);
        $criteria->compare('created_at', $this->created_at, true);
        $criteria->compare('created_by', $this->created_by);
        $criteria->compare('updated_at', $this->updated_at, true);
        $criteria->compare('updated_by', $this->updated_by);

        return new CActiveDataProvider($this, array(
                    'criteria' => $criteria,
                ));
    }

    /**
     * Returns the first two lines of this entry.
     * Used in Inbox Overview.
     *
     * @return string
     */
    public function getSnippet() {

        $snippet = "";
        $lines = explode("\n", $this->content);

        if (isset($lines[0]))
            $snippet .= $lines[0] . "\n";
        if (isset($lines[1]))
            $snippet .= $lines[1] . "\n";

        return $snippet;
    }

    /**
     * Notify User in this message entry
     */
    public function notify() {


        $senderName = $this->user->displayName;
        $senderGuid = $this->user->guid;

        foreach ($this->message->users as $user) {

            // User dont wants any emails
            if ($user->receive_email_messaging == User::RECEIVE_EMAIL_NEVER) {
                continue;
            }

            // Ignore this user itself
            if ($user->id == $this->user_id)
                continue;

            $message = new HMailMessage();
            $message->view = 'application.modules.mail.views.emails.NewMessageEntry';
            $message->addFrom(HSetting::Get('systemEmailAddress', 'mailing'), HSetting::Get('systemEmailName', 'mailing'));
            $message->addTo($user->email);
            $message->subject = 'New message in discussion from ' . $senderName;
            $message->setBody(array(
                'message' => $this->message,
                'entry' => $this,
                'user' => $user,
                'sender' => $this->user,
                'senderName' => $senderName,
                'senderGuid' => $senderGuid,
                'originator' => $this->message->originator,
                    ), 'text/html');
            Yii::app()->mail->send($message);
        }
    }

}