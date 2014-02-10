<?php

/**
 * @package humhub.modules_core.user.forms
 * @since 0.5
 * @author Luke
 */
class AccountChangePasswordForm extends CFormModel {

    public $currentPassword;
    public $newPassword;
    public $newPasswordVerify;

    /**
     * Declares the validation rules.
     */
    public function rules() {
        return array(
            array('currentPassword, newPassword, newPasswordVerify', 'required'),
            array('currentPassword', 'checkCurrentPassword'),
            array('newPassword', 'length', 'max' => 200, 'min' => 5),
            array('newPassword', 'compare', 'compareAttribute' => 'newPasswordVerify', 'message' => Yii::t('UserModule.base', 'Passwords did not match!')),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels() {
        return array(
            'currentPassword' => Yii::t('UserModule.base', 'Current password'),
            'newPassword' => Yii::t('UserModule.base', 'New password'),
            'newPasswordVerify' => Yii::t('UserModule.base', 'Retype new password'),
        );
    }

    /**
     * Form Validator which checks the current password.
     *
     * @param type $attribute
     * @param type $params
     */
    public function checkCurrentPassword($attribute, $params) {

        if ($this->$attribute != "") {

            $user = User::model()->findByPk(Yii::app()->user->id);

            if (!$user->validatePassword($this->$attribute)) {
                $this->addError($attribute, Yii::t('UserModule.base', "Current password is incorrect!"));
            }
        }
    }

    /**
     * Saves / Changes the Password
     */
    public function save() {

        if ($this->validate()) {

            $user = User::model()->findByPk(Yii::app()->user->id);
            $user->password = $this->newPassword;
            $user->save();
        }
    }

}