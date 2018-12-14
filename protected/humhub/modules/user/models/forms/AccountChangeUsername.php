<?php

	/**
	 * @link https://www.humhub.org/
	 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
	 * @license https://www.humhub.com/licences
	 */

	namespace humhub\modules\user\models\forms;

	use Yii;
	use yii\helpers\Url;
	use humhub\modules\user\models\User;
	use humhub\modules\user\components\CheckPasswordValidator;
	use humhub\modules\user\components\UsernameValidator;

	/**
	 * Form Model for username change
	 *
	 * @since 0.5
	 */
	class AccountChangeUsername extends \yii\base\Model
	{

		/**
		 * @var string the users password
		 */
		public $currentPassword;

		/**
		 * @var string the users new username
		 */
		public $newUsername;

		/**
		 * @inheritdoc
		 */
		public function rules()
		{
			/* @var $userModule \humhub\modules\user\Module */
        	$userModule = Yii::$app->getModule('user');

			$rules = [
				['newUsername', 'required'],
				['newUsername', 'string', 'max' => $userModule->maximumUsernameLength, 'min' => $userModule->minimumUsernameLength],
				['newUsername', 'unique', 'targetAttribute' => 'username', 'targetClass' => User::class, 'message' => '{attribute} "{value}" is already in use!'],				
				['newUsername', UsernameValidator::class],
				['newUsername', 'trim']
			];

			if (CheckPasswordValidator::hasPassword()) {
				$rules[] = ['currentPassword', CheckPasswordValidator::class];
				$rules[] = ['currentPassword', 'required'];
			}

			return $rules;
		}

		/**
		 * @inheritdoc
		 */
		public function attributeLabels()
		{
			return [
				'currentPassword' => Yii::t('UserModule.forms_AccountChangeUsernameForm', 'Current password'),
				'newUsername' => Yii::t('UserModule.forms_AccountChangeUsernameForm', 'New Username'),
			];
		}

		/**
		 * Sends Change Username
		 */
		public function sendChangeUsername($approveUrl = '')
		{
			$user = Yii::$app->user->getIdentity();

			$token = md5(Yii::$app->settings->get('secret') . $user->guid . $this->newUsername);

			$mail = Yii::$app->mailer->compose([
												   'html' => '@humhub/modules/user/views/mails/ChangeUsername',
												   'text' => '@humhub/modules/user/views/mails/plaintext/ChangeUsername'
											   ], [
												   'user' => $user,
												   'newUsername' => $this->newUsername,
												   'approveUrl' => Url::to([empty($approveUrl) ? "/user/account/change-username-validate" : $approveUrl, 'username' => $this->newUsername, 'token' => $token], true),
											   ]);
			$mail->setTo($user->email);
			$mail->setSubject(Yii::t('UserModule.forms_AccountChangeUsernameForm', 'Username change'));
			$mail->send();

			return true;
		}

	}
