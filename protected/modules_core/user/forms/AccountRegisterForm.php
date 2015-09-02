<?php

/**
 * Register Form just collects users e-mail and sends an invite
 *
 * @package humhub.modules_core.user.forms
 * @since 0.5
 * @author Luke
 */
class AccountRegisterForm extends CFormModel {

    public $email;

    /**
     * Declares the validation rules.
     */
    public function rules() {
        return array(
            array('email', 'required'),
            array('email', 'email'),
            array('email', 'uniqueEmailValidator'),
        );
    }

    public function uniqueEMailValidator($attribute, $params) {

        $email = User::model()->resetScope()->findByAttributes(array('email' => $this->$attribute));
        if ($email !== null) {
            $this->addError($attribute, Yii::t('UserModule.forms_AccountRegisterForm', 'E-Mail is already in use! - Try forgot password.'));
        }

    }


    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels() {
        return array(
            'email' => Yii::t('UserModule.forms_AccountRegisterForm', 'E-Mail'),
        );
    }

}