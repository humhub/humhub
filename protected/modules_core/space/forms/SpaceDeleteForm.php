<?php

/**
 *
 *
 * @author Luke
 * @package humhub.modules_core.space.forms
 * @since 0.5
 */
class SpaceDeleteForm extends CFormModel
{
	public $currentPassword;

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(

			array('currentPassword', 'required'),
			array('currentPassword', 'checkCurrentPassword'),

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
			'currentPassword'=>Yii::t('SpaceModule.base', 'Your password'),
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
				$this->addError($attribute, Yii::t('SpaceModule.base', "Your password is incorrect!"));
			}
		}
	}




}