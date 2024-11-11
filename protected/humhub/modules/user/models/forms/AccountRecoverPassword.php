<?php

namespace humhub\modules\user\models\forms;

use humhub\modules\user\models\User;
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
            // Don't display any error about not existing email for safe reason
            return;
        }

        if ($user->getPasswordRecoveryService()->isLimited()) {
            $this->addError($attribute, Yii::t('UserModule.account', 'Password recovery can only be initiated once every 10 minutes.'));
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

        if (!$user) {
            // Make the case of not existing email as successful for safe reason
            return true;
        }

        return $user->getPasswordRecoveryService()->sendRecoveryInfo();
    }

}
