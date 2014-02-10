<?php

/**
 * @package humhub.modules.mail.forms
 * @since 0.5
 */
class ReplyMessageForm extends CFormModel {

    public $message;

    /**
     * Declares the validation rules.
     */
    public function rules() {
        return array(
            array('message', 'required'),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels() {
        return array(
            'message' => Yii::t('MailModule.base', 'Message'),
        );
    }

}