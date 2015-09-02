<?php
/**
 * CTimestampBehavior class file.
 *
 * @author Jonah Turnquist <poppitypop@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CTimestampBehavior will automatically fill date and time related attributes.
 *
 * CTimestampBehavior will automatically fill date and time related attributes when the active record
 * is created and/or updated.
 * You may specify an active record model to use this behavior like so:
 * <pre>
 * public function behaviors(){
 * 	return array(
 * 		'CTimestampBehavior' => array(
 * 			'class' => 'zii.behaviors.CTimestampBehavior',
 * 			'createAttribute' => 'create_time_attribute',
 * 			'updateAttribute' => 'update_time_attribute',
 * 		)
 * 	);
 * }
 * </pre>
 * The {@link createAttribute} and {@link updateAttribute} options actually default to 'create_time' and 'update_time'
 * respectively, so it is not required that you configure them. If you do not wish CTimestampBehavior
 * to set a timestamp for record update or creation, set the corresponding attribute option to null.
 *
 * By default, the update attribute is only set on record update. If you also wish it to be set on record creation,
 * set the {@link setUpdateOnCreate} option to true.
 *
 * Although CTimestampBehavior attempts to figure out on it's own what value to inject into the timestamp attribute,
 * you may specify a custom value to use instead via {@link timestampExpression}
 *
 * @author Jonah Turnquist <poppitypop@gmail.com>
 * @package zii.behaviors
 * @since 1.1
 */

class CTimestampBehavior extends CActiveRecordBehavior {
	/**
	 * @var mixed The name of the attribute to store the creation time.  Set to null to not
	 * use a timestamp for the creation attribute.  Defaults to 'create_time'
	 */
	public $createAttribute = 'create_time';
	/**
	 * @var mixed The name of the attribute to store the modification time.  Set to null to not
	 * use a timestamp for the update attribute.  Defaults to 'update_time'
	 */
	public $updateAttribute = 'update_time';

	/**
	 * @var bool Whether to set the update attribute to the creation timestamp upon creation.
	 * Otherwise it will be left alone.  Defaults to false.
	 */
	public $setUpdateOnCreate = false;

	/**
	 * @var mixed The expression that will be used for generating the timestamp.
	 * This can be either a string representing a PHP expression (e.g. 'time()'),
	 * or a {@link CDbExpression} object representing a DB expression (e.g. new CDbExpression('NOW()')).
	 * Defaults to null, meaning that we will attempt to figure out the appropriate timestamp
	 * automatically. If we fail at finding the appropriate timestamp, then it will
	 * fall back to using the current UNIX timestamp.
	 *
	 * A PHP expression can be any PHP code that has a value. To learn more about what an expression is,
	 * please refer to the {@link http://www.php.net/manual/en/language.expressions.php php manual}.
	 */
	public $timestampExpression;

	/**
	 * @var array Maps column types to database method
	 */
	protected static $map = array(
			'datetime'=>'NOW()',
			'timestamp'=>'NOW()',
			'date'=>'NOW()',
	);

	/**
	 * Responds to {@link CModel::onBeforeSave} event.
	 * Sets the values of the creation or modified attributes as configured
	 *
	 * @param CModelEvent $event event parameter
	 */
	public function beforeSave($event) {
		if ($this->getOwner()->getIsNewRecord() && ($this->createAttribute !== null)) {
			$this->getOwner()->{$this->createAttribute} = $this->getTimestampByAttribute($this->createAttribute);
		}
		if ((!$this->getOwner()->getIsNewRecord() || $this->setUpdateOnCreate) && ($this->updateAttribute !== null)) {
			$this->getOwner()->{$this->updateAttribute} = $this->getTimestampByAttribute($this->updateAttribute);
		}
	}

	/**
	 * Gets the appropriate timestamp depending on the column type $attribute is
	 *
	 * @param string $attribute $attribute
	 * @return mixed timestamp (eg unix timestamp or a mysql function)
	 */
	protected function getTimestampByAttribute($attribute) {
		if ($this->timestampExpression instanceof CDbExpression)
			return $this->timestampExpression;
		elseif ($this->timestampExpression !== null)
			return @eval('return '.$this->timestampExpression.';');

		$columnType = $this->getOwner()->getTableSchema()->getColumn($attribute)->dbType;
		return $this->getTimestampByColumnType($columnType);
	}

	/**
	 * Returns the appropriate timestamp depending on $columnType
	 *
	 * @param string $columnType $columnType
	 * @return mixed timestamp (eg unix timestamp or a mysql function)
	 */
	protected function getTimestampByColumnType($columnType) {
		return isset(self::$map[$columnType]) ? new CDbExpression(self::$map[$columnType]) : time();
	}
}
