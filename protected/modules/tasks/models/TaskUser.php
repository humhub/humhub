<?php

/**
 * This is the model class for table "task_user".
 *
 * The followings are the available columns in table 'task_user':
 * @property integer $id
 * @property string $task_id
 * @property string $space_id
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 */
class TaskUser extends HActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return TaskUser the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'task_user';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('created_at, created_by, updated_at, updated_by, task_id, user_id', 'required'),
			array('created_by, updated_by, task_id, user_id', 'numerical', 'integerOnly'=>true),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

}