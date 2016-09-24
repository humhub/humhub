<?php

namespace humhub\modules\admin\models\forms;

use Yii;

/**
 * @package humhub.forms
 * @since 0.5
 */
class ApproveUserForm extends \yii\base\Model
{

    public $subject;
    public $message;

    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        return array(
            array(['subject', 'message'], 'required'),
        );
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array(
            'subject' => Yii::t('AdminModule.forms_ApproveUserForm', 'Subject'),
            'message' => Yii::t('AdminModule.forms_ApproveUserForm', 'Message'),
        );
    }

    public function send($email)
    {
        $mail = Yii::$app->mailer->compose(['html' => '@humhub/views/mail/TextOnly'], ['message' => $this->message]);
        $mail->setFrom([Yii::$app->settings->get('mailer.systemEmailAddress') => Yii::$app->settings->get('mailer.systemEmailName')]);
        $mail->setTo($email);
        $mail->setSubject($this->subject);
        $mail->send();
    }

}
