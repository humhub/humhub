<?php

namespace humhub\core\user\models;

use Yii;
use humhub\models\Setting;

/**
 * This is the model class for table "user_invite".
 *
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
 * @property string $language
 */
class Invite extends \yii\db\ActiveRecord
{

    const SOURCE_SELF = "self";
    const SOURCE_INVITE = "invite";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_invite';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_originator_id', 'space_invite_id', 'created_by', 'updated_by'], 'integer'],
            [['email'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['email', 'source', 'token'], 'string', 'max' => 45],
            [['language'], 'string', 'max' => 10],
            [['email'], 'unique'],
            [['token'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_originator_id' => 'User Originator ID',
            'space_invite_id' => 'Space Invite ID',
            'email' => 'Email',
            'source' => 'Source',
            'token' => 'Token',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'language' => 'Language',
        ];
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->token = uniqid() . mt_rand();
        }

        return parent::beforeSave($insert);
    }

    /**
     * Sends the invite e-mail
     *
     */
    public function sendInviteMail()
    {

        // User requested registration link by its self
        if ($this->source == self::SOURCE_SELF) {

            $mail = Yii::$app->mailer->compose(['html'=>'@humhub/core/user/views/mails/UserInviteSelf'], ['token'=>$this->token]);
            $mail->setFrom([Setting::Get('systemEmailAddress', 'mailing') => Setting::Get('systemEmailName', 'mailing')]);
            $mail->setTo($this->email);
            $mail->setSubject(Yii::t('UserModule.views_mails_UserInviteSelf', 'Registration Link'));
            $mail->send();

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
