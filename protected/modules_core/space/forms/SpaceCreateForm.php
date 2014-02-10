<?php

/**
 * Space Create Form
 *
 * @author Luke
 * @package humhub.modules_core.space.forms
 * @since 0.5
 */
class SpaceCreateForm extends CFormModel
{
	public $title;
	public $type;

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(

			array('title, type', 'required'),
			array('title', 'checkTitle'),

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
			'title'=>Yii::t('SpaceModule.base', 'Title'),
			'type'=>Yii::t('SpaceModule.base', 'Type'),
		);
	}

	/**
	 * Checks if Space Title is already in use
	 *
	 * @param type $attribute
	 * @param type $params
	 */
	public function checkTitle($attribute, $params) {
		if ($this->$attribute != "") {
			$space = Space::model()->findByAttributes(array('name'=>$this->title));
			if ($space !== null) {
				$this->addError($attribute, Yii::t('SpaceModule.base', "Space title is already in use!"));
			}
		}
	}




}