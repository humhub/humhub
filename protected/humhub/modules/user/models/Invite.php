<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;

use humhub\components\access\ControllerAccess;
use humhub\components\ActiveRecord;
use humhub\modules\space\models\Space;
use humhub\modules\user\Module;
use Yii;
use yii\captcha\CaptchaValidator;
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
 * @property string $captcha
 *
 * @property Space $space
 */
class Invite extends ActiveRecord
{

    const SOURCE_SELF = 'self';
    const SOURCE_INVITE = 'invite';
    const SOURCE_INVITE_BY_LINK = 'invite_by_link';
    const EMAIL_TOKEN_LENGTH = 12;
    const LINK_TOKEN_LENGTH = 14; // Should be different that EMAIL_TOKEN_LENGTH

    public $captcha;

    /**
     * @var bool
     */
    public $skipCaptchaValidation = false;

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
            [['source', 'token'], 'string', 'max' => 254],
            [['email'], 'string', 'max' => 150],
            [['language'], 'string', 'max' => 10],
            [['email'], 'required'],
            [['email'], 'unique'],
            [['email'], 'email'],
            [['captcha'], 'captcha', 'captchaAction' => 'user/auth/captcha', 'on' => static::SOURCE_INVITE],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['invite'] = ['email'];

        if ($this->showCaptureInRegisterForm()) {
            $scenarios['invite'][] = 'captcha';
        }

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email' => Yii::t('UserModule.invite', 'Email'),
            'created_at' => Yii::t('UserModule.base', 'Created at'),
            'source' => Yii::t('UserModule.base', 'Source'),
            'language' => Yii::t('base', 'Language'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($insert && $this->token == '') {
            $this->token = Yii::$app->security->generateRandomString(self::EMAIL_TOKEN_LENGTH);
        }

        return parent::beforeSave($insert);
    }

    public function selfInvite()
    {
        if (Yii::$app->settings->get('maintenanceMode')) {
            Yii::$app->getView()->warn(ControllerAccess::getMaintenanceModeWarningText());
            return false;
        }

        $this->source = self::SOURCE_SELF;
        $this->language = Yii::$app->language;

        // Delete existing invite for e-mail - but reuse token
        $existingInvite = Invite::findOne(['email' => $this->email]);
        if ($existingInvite !== null) {
            $this->token = $existingInvite->token;
            $existingInvite->delete();
        }

        if (!$this->allowSelfInvite()) {
            return false;
        }

        if ($this->isRegisteredUser()) {
            if ($this->sendAlreadyRegisteredUserMail()) {
                $this->refreshCaptchaCode();
                return true;
            }
            return false;
        }

        if ($this->save()) {
            return $this->sendInviteMail();
        }

        return false;
    }

    /**
     * Sends the invite e-mail
     *
     * @return bool
     */
    public function sendInviteMail(): bool
    {
        /** @var Module $module */
        $module = Yii::$app->moduleManager->getModule('user');
        $registrationUrl = Url::to(['/user/registration', 'token' => $this->token], true);

        $result = false;

        // User requested registration link by its self
        if ($this->source === self::SOURCE_SELF || $this->source === self::SOURCE_INVITE_BY_LINK) {
            $mail = Yii::$app->mailer->compose([
                'html' => '@humhub/modules/user/views/mails/UserInviteSelf',
                'text' => '@humhub/modules/user/views/mails/plaintext/UserInviteSelf'
            ], [
                'token' => $this->token,
                'registrationUrl' => $registrationUrl
            ]);
            $mail->setTo($this->email);
            $mail->setSubject(Yii::t('UserModule.base', 'Welcome to %appName%', ['%appName%' => Yii::$app->name]));
            $result = $mail->send();
        } elseif ($this->source == self::SOURCE_INVITE && $this->space !== null) {
            if ($module->sendInviteMailsInGlobalLanguage) {
                Yii::$app->setLanguage(Yii::$app->settings->get('defaultLanguage'));
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
            $mail->setSubject(Yii::t('UserModule.base', 'You\'ve been invited to join {space} on {appName}', ['space' => $this->space->name, 'appName' => Yii::$app->name]));
            $result = $mail->send();

            // Switch back to users language
            Yii::$app->setLanguage(Yii::$app->user->language);
        } elseif ($this->source == self::SOURCE_INVITE) {

            // Switch to systems default language
            if ($module->sendInviteMailsInGlobalLanguage) {
                Yii::$app->setLanguage(Yii::$app->settings->get('defaultLanguage'));
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
            $result = $mail->send();

            // Switch back to users language
            Yii::$app->setLanguage(Yii::$app->user->language);
        }

        return $result;
    }

    /**
     * Check if user already registered with the requested email
     *
     * @return bool
     */
    private function isRegisteredUser(): bool
    {
        return User::find()->where(['email' => $this->email])->exists();
    }


    /**
     * Sends e-mail for user which already is registered with requested email
     *
     * @return bool
     */
    public function sendAlreadyRegisteredUserMail(): bool
    {
        $mail = Yii::$app->mailer->compose([
            'html' => '@humhub/modules/user/views/mails/UserAlreadyRegistered',
            'text' => '@humhub/modules/user/views/mails/plaintext/UserAlreadyRegistered'
        ], [
            'passwordRecoveryUrl' => Url::to(['/user/password-recovery'], true)
        ]);
        $mail->setTo($this->email);
        $mail->setSubject(Yii::t('UserModule.base', 'Welcome to %appName%', ['%appName%' => Yii::$app->name]));

        return $mail->send();
    }

    private function refreshCaptchaCode()
    {
        foreach ($this->getActiveValidators('captcha') as $validator) {
            if ($validator instanceof CaptchaValidator) {
                $captchaValidator = $validator;
                break;
            }
        }

        if (isset($captchaValidator)) {
            $captchaValidator->createCaptchaAction()->getVerifyCode(true);
        }
    }

    /**
     * Return user which triggered this invite
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOriginator()
    {
        return $this->hasOne(User::class, ['id' => 'user_originator_id']);
    }

    /**
     * Return space which is involved in this invite
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSpace()
    {
        return $this->hasOne(Space::class, ['id' => 'space_invite_id']);
    }

    /**
     * Allow users to invite themself
     *
     * @return boolean allow self invite
     */
    public function allowSelfInvite()
    {
        return (!Yii::$app->settings->get('maintenanceMode') && Yii::$app->getModule('user')->settings->get('auth.anonymousRegistration'));
    }

    /**
     * @return bool
     */
    public function showCaptureInRegisterForm()
    {
        return
            !$this->skipCaptchaValidation
            && (Yii::$app->getModule('user')->settings->get('auth.showCaptureInRegisterForm'));
    }
}
