<?php

namespace humhub\modules\user\models\forms;

use humhub\libs\SafeBaseUrl;
use humhub\modules\user\models\User;
use humhub\modules\user\authclient\Password;
use humhub\libs\UUID;
use Yii;
use yii\base\Model;

/**
 * @package humhub.modules_core.user.forms
 * @since 0.5
 * @author Luke
 */
class AccountRecoverPassword extends Model
{

    public $verifyCode;
    public $email;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return [
            ['email', 'required'],
            ['email', 'email'],
            ['verifyCode', 'captcha', 'captchaAction' => '/user/auth/captcha'],
            ['email', 'verifyEmail'],
        ];
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels()
    {
        return [
            'email' => Yii::t('UserModule.account', 'E-Mail'),
        ];
    }

    /**
     * Checks email for existing and if it can be recovered
     */
    public function verifyEmail($attribute)
    {
        if ($this->getErrors('verifyCode')) {
            // Don't start to check email while captcha code is wrong
            return;
        }

        $user = User::findOne(['email' => $this->email]);

        if ($user === null) {
            $this->addError($attribute, Yii::t('UserModule.account', Yii::t('UserModule.account', '{attribute} "{value}" was not found!', [
                'attribute' => $this->getAttributeLabel($attribute),
                'value' => $this->email
            ])));
            return;
        }

        // Checks if we can recover users password.
        // This may not possible on e.g. LDAP accounts.
        $passwordAuth = new Password();
        if ($user->auth_mode !== $passwordAuth->getId()) {
            $this->addError($attribute, Yii::t('UserModule.account', Yii::t('UserModule.account', 'Password recovery is not possible on your account type!')));
        }
    }

    /**
     * Sends this user a new password by E-Mail
     *
     */
    public function recover(): bool
    {
        $user = User::findOne(['email' => $this->email]);
        if (!$user) {
            return false;
        }

        // Switch to users language - if specified
        Yii::$app->setLanguage($user->language);

        $token = UUID::v4();
        Yii::$app->getModule('user')->settings->contentContainer($user)->set('passwordRecoveryToken', $token . '.' . time());

        $mail = Yii::$app->mailer->compose([
            'html' => '@humhub/modules/user/views/mails/RecoverPassword',
            'text' => '@humhub/modules/user/views/mails/plaintext/RecoverPassword'
        ], [
            'user' => $user,
            'linkPasswordReset' => SafeBaseUrl::to(['/user/password-recovery/reset', 'token' => $token, 'guid' => $user->guid], true)
        ]);
        $mail->setTo($user->email);
        $mail->setSubject(Yii::t('UserModule.account', 'Password Recovery'));
        return $mail->send();
    }

}
