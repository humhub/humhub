<?php

/**
 * @package humhub.modules_core.admin.forms
 * @since 0.5
 */
class MailingDefaultsForm extends CFormModel {

    public $receive_email_activities;
    public $receive_email_notifications;

    /**
     * Declares the validation rules.
     */
    public function rules() {
        return array(
            array('receive_email_notifications, receive_email_activities', 'numerical', 'integerOnly' => true),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels() {
        return array(
        );
    }

}