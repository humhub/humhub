<?php


/**
 * @package humhub.modules_core.user.forms
 * @since 0.5
 * @author Luke
 */
class AccountChangeEmailForm extends CFormModel
{
	public $currentPassword;
	public $newEmail;

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
			array('currentPassword, newEmail', 'required'),
			array('currentPassword', 'checkCurrentPassword'),
			array('newEmail', 'email'),
			array('newEmail', 'unique', 'attributeName'=>'email', 'caseSensitive'=>false, 'className' => 'User', 'message' => '{attribute} "{value}" is already in use!'),
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
			'currentPassword'=>Yii::t('UserModule.base', 'Current password'),
			'newEmail'=>Yii::t('UserModule.base', 'New E-Mail address'),
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
	 * Sends Change E-Mail E-Mail
	 *
	 */
	public function sendChangeEmail() {

		if ($this->validate()) {

			$user = User::model()->findByPk(Yii::app()->user->id);

			$token = md5(HSetting::Get('secret').$user->guid.$this->newEmail);

			$message = new HMailMessage();
			$message->view = "application.modules_core.user.views.mails.ChangeEmail";
			$message->addFrom(HSetting::Get('systemEmailAddress', 'mailing'), HSetting::Get('systemEmailName', 'mailing'));
			$message->addTo($this->newEmail);
			$message->subject = Yii::t('UserModule.base', 'E-Mail change');
			$message->setBody(array('user'=>$user, 'newEmail'=>$this->newEmail, 'token'=>$token), 'text/html');
			Yii::app()->mail->send($message);

		}

	}

}