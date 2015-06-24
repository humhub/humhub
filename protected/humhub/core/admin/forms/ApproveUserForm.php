<?php

/**
 * @package humhub.forms
 * @since 0.5
 */
class ApproveUserForm extends CFormModel {
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
			array('subject,message', 'required'),
		);
	}

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels()
	{
		return array(
			'subject'=>Yii::t('AdminModule.forms_ApproveUserForm', 'Subject'),
			'message'=>Yii::t('AdminModule.forms_ApproveUserForm', 'Message'),
		);
	}


	public function send($email) {


		$message = new HMailMessage();
		$message->addFrom(HSetting::Get('systemEmailAddress', 'mailing'),HSetting::Get('systemEmailName', 'mailing'));
		$message->addTo($email);
		$message->view = "application.views.mail.TextOnly";
		$message->subject = $this->subject;
		$message->setBody(array('message'=>$this->message), 'text/html');
		Yii::app()->mail->send($message);

	}

}
