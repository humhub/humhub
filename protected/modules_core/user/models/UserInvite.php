<?php

/**
 * This is the model class for table "user_invite".
 *
 * The followings are the available columns in table 'user_invite':
 * @property integer $id
 * @property integer $user_originator_id
 * @property integer $space_invite_id
 * @property string $email
 * @property string $source
 * @property string $token
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 * The followings are the available model relations:
 * @property User[] $users
 * @property Space $workspaceInvite
 * @property User $userOriginator
 *
 * @package humhub.modules_core.user.models
 * @since 0.5
 * @author Luke
 */
class UserInvite extends HActiveRecord {

    const SOURCE_SELF = "self";
    const SOURCE_INVITE = "invite";

    /**
     * Generates a unique token before save
     *
     * @return type
     */
    protected function beforeSave() {

        if ($this->isNewRecord) {
            $this->token = uniqid() . mt_rand();
        }
        return parent::beforeSave();
    }

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return UserInvite the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'user_invite';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('email', 'required'),
            array('email', 'email'),
            array('email', 'unique'),
            array('user_originator_id, space_invite_id, created_by, updated_by', 'numerical', 'integerOnly' => true),
            array('email, source, token', 'length', 'max' => 45),
            array('created_at, updated_at', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, user_originator_id, space_invite_id, email, source, token, created_at, created_by, updated_at, updated_by', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'workspaceInvite' => array(self::BELONGS_TO, 'Space', 'space_invite_id'),
            'userOriginator' => array(self::BELONGS_TO, 'User', 'user_originator_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'user_originator_id' => 'User Originator',
            'space_invite_id' => 'Space Invite',
            'email' => 'Email',
            'source' => 'Source',
            'token' => 'Token',
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
        $criteria->compare('user_originator_id', $this->user_originator_id);
        $criteria->compare('space_invite_id', $this->space_invite_id);
        $criteria->compare('email', $this->email, true);
        $criteria->compare('source', $this->source, true);
        $criteria->compare('token', $this->token, true);
        $criteria->compare('created_at', $this->created_at, true);
        $criteria->compare('created_by', $this->created_by);
        $criteria->compare('updated_at', $this->updated_at, true);
        $criteria->compare('updated_by', $this->updated_by);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * Sends the invite e-mail
     *
     */
    public function sendInviteMail() {

        // User requested registration link by its self
        if ($this->source == self::SOURCE_SELF) {

            $message = new HMailMessage();
            $message->view = "application.modules_core.user.views.mails.UserInviteSelf";
            $message->addFrom(HSetting::Get('systemEmailAddress', 'mailing'), HSetting::Get('systemEmailName', 'mailing'));
            $message->addTo($this->email);
            $message->subject = Yii::t('UserModule.views_mails_UserInviteSelf', 'Registration Link');
            $message->setBody(array('token' => $this->token), 'text/html');
            Yii::app()->mail->send($message);
        } elseif ($this->source == self::SOURCE_INVITE) {

            // Switch to systems default language
            Yii::app()->language = HSetting::Get('defaultLanguage');
            
            $message = new HMailMessage();
            $message->view = "application.modules_core.user.views.mails.UserInviteSpace";
            $message->addFrom(HSetting::Get('systemEmailAddress', 'mailing'), HSetting::Get('systemEmailName', 'mailing'));
            $message->addTo($this->email);
            $message->subject = Yii::t('UserModule.views_mails_UserInviteSpace', 'Space Invite');
            $message->setBody(array(
                'originator' => $this->userOriginator,
                'originatorName' => $this->userOriginator->displayName,
                'token' => $this->token,
                'workspaceName' => $this->workspaceInvite->name,
                    ), 'text/html');
            Yii::app()->mail->send($message);
            
            // Switch back to users language
            if (Yii::app()->user->language !== "") {
                Yii::app()->language = Yii::app()->user->language; 
            }
            
        }
    }

}