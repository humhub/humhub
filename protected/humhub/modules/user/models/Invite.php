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
use yii\db\ActiveQuery;
use yii\helpers\Url;

/**
 * This is the model class for table "user_invite".
 *
 * @property int $id
 * @property int $user_originator_id
 * @property int $space_invite_id
 * @property string $email
 * @property string $source
 * @property string $token
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 * @property string $language
 * @property string $firstname
 * @property string $lastname
 * @property string $captcha
 *
 * @property-read Space $space
 * @property-read User|null $originator
 * @property-read User|null $createdBy
 * @property-read User|null $updatedBy
 */
class Invite extends ActiveRecord
{
    public const SOURCE_SELF = 'self';
    public const SOURCE_INVITE = 'invite';
    public const SOURCE_INVITE_BY_LINK = 'invite_by_link';
    public const SCENARIO_INVITE = 'invite';
    public const SCENARIO_INVITE_BY_LINK_FORM = 'invite_by_link_form';
    public const EMAIL_TOKEN_LENGTH = 12;
    public const LINK_TOKEN_LENGTH = 14; // Should be different that EMAIL_TOKEN_LENGTH

    public $captcha;

    /**
     * @var bool
     */
    public $skipCaptchaValidation = false;

    protected ?array $allowedSources = null;

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
            [['language'], 'string', 'max' => 20],
            [['email'], 'required'],
            [['email'], 'unique', 'except' => self::SCENARIO_INVITE_BY_LINK_FORM],
            [['email'], 'email'],
            [['captcha'], 'captcha', 'captchaAction' => 'user/auth/captcha', 'on' => [self::SCENARIO_INVITE, self::SCENARIO_INVITE_BY_LINK_FORM]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_INVITE] = ['email'];
        $scenarios[self::SCENARIO_INVITE_BY_LINK_FORM] = ['email'];

        if ($this->showCaptureInRegisterForm()) {
            $scenarios[self::SCENARIO_INVITE][] = 'captcha';
            $scenarios[self::SCENARIO_INVITE_BY_LINK_FORM][] = 'captcha';
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
                'text' => '@humhub/modules/user/views/mails/plaintext/UserInviteSelf',
            ], [
                'registrationUrl' => $registrationUrl,
            ]);
            $mail->setTo($this->email);
            $mail->setSubject(Yii::t('UserModule.base', 'Welcome to %appName%', ['%appName%' => Yii::$app->name]));
            $result = $mail->send();
        } elseif ($this->source === self::SOURCE_INVITE && $this->space !== null) {
            if ($module->sendInviteMailsInGlobalLanguage) {
                Yii::$app->setLanguage(Yii::$app->settings->get('defaultLanguage'));
            }

            $mail = Yii::$app->mailer->compose([
                'html' => '@humhub/modules/user/views/mails/UserInviteSpace',
                'text' => '@humhub/modules/user/views/mails/plaintext/UserInviteSpace',
            ], [
                'originator' => $this->originator,
                'originatorName' => $this->originator->displayName,
                'space' => $this->space,
                'registrationUrl' => $registrationUrl,
            ]);
            $mail->setTo($this->email);
            $mail->setSubject(Yii::t('UserModule.base', 'You\'ve been invited to join {space} on {appName}', ['space' => $this->space->name, 'appName' => Yii::$app->name]));
            $result = $mail->send();

            // Switch back to users language
            Yii::$app->setLanguage(Yii::$app->user->language);
        } elseif ($this->source === self::SOURCE_INVITE) {

            // Switch to systems default language
            if ($module->sendInviteMailsInGlobalLanguage) {
                Yii::$app->setLanguage(Yii::$app->settings->get('defaultLanguage'));
            }

            $mail = Yii::$app->mailer->compose([
                'html' => '@humhub/modules/user/views/mails/UserInvite',
                'text' => '@humhub/modules/user/views/mails/plaintext/UserInvite',
            ], [
                'originator' => $this->originator,
                'originatorName' => $this->originator->displayName,
                'registrationUrl' => $registrationUrl,
            ]);
            $mail->setTo($this->email);
            $mail->setSubject(Yii::t('UserModule.invite', 'You\'ve been invited to join %appName%', ['%appName%' => Yii::$app->name]));
            $result = $mail->send();

            // Switch back to users language
            Yii::$app->setLanguage(Yii::$app->user->language);
        }

        if ($result) {
            // Refresh the updated_at timestamp
            $this->save();
        }

        return $result;
    }

    /**
     * Check if user already registered with the requested email
     *
     * @return bool
     */
    public function isRegisteredUser(): bool
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
            'text' => '@humhub/modules/user/views/mails/plaintext/UserAlreadyRegistered',
        ], [
            'passwordRecoveryUrl' => Url::to(['/user/password-recovery'], true),
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
     * @return ActiveQuery
     */
    public function getOriginator()
    {
        return $this->hasOne(User::class, ['id' => 'user_originator_id']);
    }

    /**
     * Return space which is involved in this invite
     *
     * @return ActiveQuery
     */
    public function getSpace()
    {
        return $this->hasOne(Space::class, ['id' => 'space_invite_id']);
    }

    /**
     * Allow users to invite themself
     *
     * @return bool allow self invite
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
             && (Yii::$app->getModule('user')->enableRegistrationFormCaptcha);
    }

    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    public function getAllowedSources(): array
    {
        if ($this->allowedSources === null) {
            $this->allowedSources = [
                self::SOURCE_INVITE => Yii::t('AdminModule.base', 'Invite by email'),
                self::SOURCE_INVITE_BY_LINK => Yii::t('AdminModule.base', 'Invite by link'),
                self::SOURCE_SELF => Yii::t('AdminModule.base', 'Sign up'),
            ];
        }

        return $this->allowedSources;
    }

    public static function filterSource(): array
    {
        return ['source' => array_keys((new static())->getAllowedSources())];
    }
}
