<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;

use Yii;

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
 * @property string $firstname
 * @property string $lastname
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
            [['created_at', 'updated_at', 'firstname', 'lastname'], 'safe'],
            [['email', 'source', 'token'], 'string', 'max' => 45],
            [['language'], 'string', 'max' => 10],
            [['email'], 'unique'],
            [['token'], 'unique'],
            [['firstname', 'lastname'], 'string', 'max' => 255],
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
            'firstname' => 'First name',
            'lastname' => 'Last name'
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

            $mail = Yii::$app->mailer->compose([
                'html' => '@humhub/modules/user/views/mails/UserInviteSelf',
                'text' => '@humhub/modules/user/views/mails/plaintext/UserInviteSelf'
            ], ['token' => $this->token]);
            $mail->setFrom([\humhub\models\Setting::Get('systemEmailAddress', 'mailing') => \humhub\models\Setting::Get('systemEmailName', 'mailing')]);
            $mail->setTo($this->email);
            $mail->setSubject(Yii::t('UserModule.views_mails_UserInviteSelf', 'Registration Link'));
            $mail->send();
        } elseif ($this->source == self::SOURCE_INVITE) {

            // Switch to systems default language
            Yii::$app->language = \humhub\models\Setting::Get('defaultLanguage');

            $mail = Yii::$app->mailer->compose([
                'html' => '@humhub/modules/user/views/mails/UserInviteSpace',
                'text' => '@humhub/modules/user/views/mails/plaintext/UserInviteSpace'
            ], [
                'token' => $this->token,
                'originator' => $this->originator,
                'originatorName' => $this->originator->displayName,
                'token' => $this->token,
                'space' => $this->space
            ]);
            $mail->setFrom([\humhub\models\Setting::Get('systemEmailAddress', 'mailing') => \humhub\models\Setting::Get('systemEmailName', 'mailing')]);
            $mail->setTo($this->email);
            $mail->setSubject(Yii::t('UserModule.views_mails_UserInviteSpace', 'Space Invite'));
            $mail->send();

            // Switch back to users language
            if (Yii::$app->user->language !== "") {
                Yii::$app->language = Yii::$app->user->language;
            }
        }
    }

    public function getOriginator()
    {
        return $this->hasOne(\humhub\modules\user\models\User::className(), ['id' => 'user_originator_id']);
    }

    public function getSpace()
    {
        return $this->hasOne(\humhub\modules\space\models\Space::className(), ['id' => 'space_invite_id']);
    }

}
