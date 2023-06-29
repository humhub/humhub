<?php

namespace humhub\modules\user\models\forms;

use humhub\modules\user\models\User;
use humhub\modules\user\authclient\Password;
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

        if ($user->getPasswordRecoveryService()->isLimited()) {
            $this->addError($attribute, Yii::t('UserModule.account', Yii::t('UserModule.account', 'Password recovery can only be initiated once every 10 minutes.')));
            return;
        }

        // Checks if we can recover users password.
        // This may not possible on e.g. LDAP accounts.
        $passwordAuth = new Password();
        if ($user->auth_mode !== $passwordAuth->getId()) {
            $this->addError($attribute, Yii::t('UserModule.account', Yii::t('UserModule.account', 'Password recovery disabled. Please contact your system administrator.')));
        }
    }

    /**
     * Sends this user a new password by E-Mail
     *
     * @return bool
     */
    public function recover(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $user = User::findOne(['email' => $this->email]);

        return $user && $user->getPasswordRecoveryService()->sendRecoveryInfo();
    }

}
