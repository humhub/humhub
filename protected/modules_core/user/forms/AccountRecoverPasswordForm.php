<?php

/**
 * @package humhub.modules_core.user.forms
 * @since 0.5
 * @author Luke
 */
class AccountRecoverPasswordForm extends CFormModel {

    public $verifyCode;
    public $email;


    /**
     * Declares the validation rules.
     */
    public function rules() {
        return array(
            array('email', 'required'),
            array('email', 'email'),
            array('email', 'canRecoverPassword'),
            array('verifyCode', 'captcha', 'allowEmpty' => !CCaptcha::checkRequirements()),
            array('email', 'exist', 'className' => 'User', 'message' => Yii::t('UserModule.base', '{attribute} "{value}" was not found!')),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels() {
        return array(
            'email' => Yii::t('UserModule.base', 'E-Mail'),
        );
    }

    /**
     * Checks if we can recover users password.
     * This may not possible on e.g. LDAP accounts.
     */
    public function canRecoverPassword($attribute, $params) {

        if ($this->email != "") {
            $user = User::model()->findByAttributes(array('email' => $this->email));
            if ($user != null && $user->auth_mode != "local") {
                $this->addError($attribute, Yii::t('UserModule.base', Yii::t('UserModule.base', "Password recovery is not possible on your account type!")));
            }
        }
    }

    /**
     * Sends this user a new password by E-Mail
     *
     */
    public function recoverPassword() {

        $user = User::model()->findByAttributes(array('email' => $this->email));

        // Switch to users language
        Yii::app()->language = Yii::app()->user->language;

        // Set New Password
        $userPassword = new UserPassword();
        $userPassword->user_id = $user->id;
        $newPassword = $userPassword->setRandomPassword();
        $userPassword->save();

        $message = new HMailMessage();
        $message->view = "application.modules_core.user.views.mails.RecoverPassword";
        $message->addFrom(HSetting::Get('systemEmailAddress', 'mailing'), HSetting::Get('systemEmailName', 'mailing'));
        $message->addTo($this->email);
        $message->subject = Yii::t('UserModule.base', 'Password Recovery');
        $message->setBody(array('user' => $user, 'newPassword' => $newPassword), 'text/html');
        Yii::app()->mail->send($message);
    }

}
