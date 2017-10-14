<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;

use humhub\components\ActiveRecord;
use Yii;
use yii\helpers\Url;

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
class Invite extends ActiveRecord
{

    const SOURCE_SELF = "self";
    const SOURCE_INVITE = "invite";
    const TOKEN_LENGTH = 12;

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
            [['user_originator_id', 'space_invite_id'], 'integer'],
            [['token'], 'unique'],
            [['firstname', 'lastname'], 'string', 'max' => 255],
            [['email', 'source', 'token'], 'string', 'max' => 45],
            [['language'], 'string', 'max' => 10],
            [['email'], 'required'],
            [['email'], 'unique'],
            [['email'], 'email'],
            [['email'], 'unique', 'targetClass' => \humhub\modules\user\models\User::className(), 'message' => Yii::t('UserModule.base', 'E-Mail is already in use! - Try forgot password.')],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['invite'] = ['email'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email' => Yii::t('UserModule.models_Invite', 'Email'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($insert && $this->token == '') {
            $this->token = Yii::$app->security->generateRandomString(self::TOKEN_LENGTH);
        }

        return parent::beforeSave($insert);
    }

    public function selfInvite()
    {
        $this->source = self::SOURCE_SELF;
        $this->language = Yii::$app->language;

        // Delete existing invite for e-mail - but reuse token
        $existingInvite = Invite::findOne(['email' => $this->email]);
        if ($existingInvite !== null) {
            $this->token = $existingInvite->token;
            $existingInvite->delete();
        }

        if ($this->allowSelfInvite() && $this->validate() && $this->save()) {
            $this->sendInviteMail();
            return true;
        }

        return false;
    }

    /**
     * Sends the invite e-mail
     */
    public function sendInviteMail()
    {
        $module = Yii::$app->moduleManager->getModule('user');
        $registrationUrl = Url::to(['/user/registration', 'token' => $this->token], true);

        // User requested registration link by its self
        if ($this->source == self::SOURCE_SELF) {

            $mail = Yii::$app->mailer->compose([
                'html' => '@humhub/modules/user/views/mails/UserInviteSelf',
                'text' => '@humhub/modules/user/views/mails/plaintext/UserInviteSelf'
                    ], [
                'token' => $this->token,
                'registrationUrl' => $registrationUrl
            ]);
            $mail->setTo($this->email);
            $mail->setSubject(Yii::t('UserModule.views_mails_UserInviteSelf', 'Welcome to %appName%', ['%appName%' => Yii::$app->name]));
            $mail->send();
        } elseif ($this->source == self::SOURCE_INVITE && $this->space !== null) {

            if($module->sendInviteMailsInGlobalLanguage) {
                Yii::$app->language = Yii::$app->settings->get('defaultLanguage');
            }

            $mail = Yii::$app->mailer->compose([
                'html' => '@humhub/modules/user/views/mails/UserInviteSpace',
                'text' => '@humhub/modules/user/views/mails/plaintext/UserInviteSpace'
                    ], [
                'token' => $this->token,
                'originator' => $this->originator,
                'originatorName' => $this->originator->displayName,
                'space' => $this->space,
                'registrationUrl' => $registrationUrl
            ]);
            $mail->setTo($this->email);
            $mail->setSubject(Yii::t('UserModule.views_mails_UserInviteSpace', 'You\'ve been invited to join {space} on {appName}', ['space' => $this->space->name, 'appName' => Yii::$app->name]));
            $mail->send();

            // Switch back to users language
            if (Yii::$app->user->language !== "") {
                Yii::$app->language = Yii::$app->user->language;
            }
        } elseif ($this->source == self::SOURCE_INVITE) {

            // Switch to systems default language
            if($module->sendInviteMailsInGlobalLanguage) {
                Yii::$app->language = Yii::$app->settings->get('defaultLanguage');
            }

            $mail = Yii::$app->mailer->compose([
                'html' => '@humhub/modules/user/views/mails/UserInvite',
                'text' => '@humhub/modules/user/views/mails/plaintext/UserInvite'
                    ], [
                'originator' => $this->originator,
                'originatorName' => $this->originator->displayName,
                'token' => $this->token,
                'registrationUrl' => $registrationUrl
            ]);
            $mail->setTo($this->email);
            $mail->setSubject(Yii::t('UserModule.invite', 'You\'ve been invited to join %appName%', ['%appName%' => Yii::$app->name]));
            $mail->send();

            // Switch back to users language
            if (Yii::$app->user->language !== "") {
                Yii::$app->language = Yii::$app->user->language;
            }
        }
    }

    /**
     * Return user which triggered this invite
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOriginator()
    {
        return $this->hasOne(\humhub\modules\user\models\User::className(), ['id' => 'user_originator_id']);
    }

    /**
     * Return space which is involved in this invite
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSpace()
    {
        return $this->hasOne(\humhub\modules\space\models\Space::className(), ['id' => 'space_invite_id']);
    }

    /**
     * Allow users to invite themself
     *
     * @return boolean allow self invite
     */
    public function allowSelfInvite()
    {
        return (Yii::$app->getModule('user')->settings->get('auth.anonymousRegistration'));
    }

}
