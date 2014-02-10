<?php

/**
 * @package humhub.modules.mail.forms
 * @since 0.5
 */
class InviteRecipientForm extends CFormModel {

    public $recipient;
    public $message; // message

    /**
     * Parsed recipients in array of user objects
     *
     * @var type
     */
    public $recipients = array();

    /**
     * Declares the validation rules.
     */
    public function rules() {
        return array(
            array('recipient', 'required'),
            array('recipient', 'checkRecipient')
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels() {
        return array(
            'recipient' => 'Recipient',
        );
    }

    /**
     * Form Validator which checks the recipient field
     *
     * @param type $attribute
     * @param type $params
     */
    public function checkRecipient($attribute, $params) {

        // Check if email field is not empty
        if ($this->$attribute != "") {

            $recipients = explode(",", $this->$attribute);

            foreach ($recipients as $userGuid) {
                $userGuid = preg_replace("/[^A-Za-z0-9\-]/", '', $userGuid);

                // Try load user
                $user = User::model()->findByAttributes(array('guid' => $userGuid));
                if ($user != null) {

                    if ($user->id == Yii::app()->user->id) {
                        $this->addError($attribute, Yii::t('MailModule.base', "You could not send an email to yourself!"));
                    } else {
                        $this->recipients[] = $user;
                    }
                }
            }
        }
    }

    /**
     * Returns an Array with selected recipients
     */
    public function getRecipients() {
        return $this->recipients;
    }

}