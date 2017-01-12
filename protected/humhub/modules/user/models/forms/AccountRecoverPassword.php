<?php

namespace humhub\modules\user\models\forms;

use Yii;
use yii\helpers\Url;
use humhub\modules\user\models\User;

/**
 * @package humhub.modules_core.user.forms
 * @since 0.5
 * @author Luke
 */
class AccountRecoverPassword extends \yii\base\Model
{

    public $verifyCode;
    public $email;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array('email', 'required'),
            array('email', 'email'),
            array('email', 'canRecoverPassword'),
            array('verifyCode', 'captcha', 'captchaAction' => '/user/auth/captcha'),
            array('email', 'exist', 'targetClass' => User::className(), 'targetAttribute' => 'email', 'message' => Yii::t('UserModule.forms_AccountRecoverPasswordForm', '{attribute} "{value}" was not found!')),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels()
    {
        return array(
            'email' => Yii::t('UserModule.forms_AccountRecoverPasswordForm', 'E-Mail'),
        );
    }

    /**
     * Checks if we can recover users password.
     * This may not possible on e.g. LDAP accounts.
     */
    public function canRecoverPassword($attribute, $params)
    {

        if ($this->email != "") {
            $user = User::findOne(array('email' => $this->email));
            $passwordAuth = new \humhub\modules\user\authclient\Password();

            if ($user != null && $user->auth_mode !== $passwordAuth->getId()) {
                $this->addError($attribute, Yii::t('UserModule.forms_AccountRecoverPasswordForm', Yii::t('UserModule.forms_AccountRecoverPasswordForm', "Password recovery is not possible on your account type!")));
            }
        }
    }

    /**
     * Sends this user a new password by E-Mail
     *
     */
    public function recover()
    {

        $user = User::findOne(array('email' => $this->email));

        // Switch to users language - if specified
        if ($user->language !== "") {
            Yii::$app->language = $user->language;
        }

        $token = \humhub\libs\UUID::v4();
        Yii::$app->getModule('user')->settings->contentContainer($user)->set('passwordRecoveryToken', $token . '.' . time());

        $mail = Yii::$app->mailer->compose([
			'html' => '@humhub/modules/user/views/mails/RecoverPassword',
			'text' => '@humhub/modules/user/views/mails/plaintext/RecoverPassword'
		], [
            'user' => $user,
            'linkPasswordReset' => Url::to(["/user/password-recovery/reset", 'token' => $token, 'guid' => $user->guid], true)
        ]);
        $mail->setTo($user->email);
        $mail->setSubject(Yii::t('UserModule.forms_AccountRecoverPasswordForm', 'Password Recovery'));
        $mail->send();

        return true;
    }

}
