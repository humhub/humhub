<?php
/**
 * CTypeValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CTypeValidator verifies if the attribute is of the type specified by {@link type}.
 *
 * The following data types are supported:
 * <ul>
 * <li><b>integer</b> A 32-bit signed integer data type.</li>
 * <li><b>float</b> A double-precision floating point number data type.</li>
 * <li><b>string</b> A string data type.</li>
 * <li><b>array</b> An array value. </li>
 * <li><b>date</b> A date data type.</li>
 * <li><b>time</b> A time data type.</li>
 * <li><b>datetime</b> A date and time data type.</li>
 * </ul>
 *
 * For <b>date</b> type, the property {@link dateFormat}
 * will be used to determine how to parse the date string. If the given date
 * value doesn't follow the format, the attribute is considered as invalid.
 *
 * Starting from version 1.1.7, we have a dedicated date validator {@link CDateValidator}.
 * Please consider using this validator to validate a date-typed value.
 *
 * When using the {@link message} property to define a custom error message, the message
 * may contain additional placeholders that will be replaced with the actual content. In addition
 * to the "{attribute}" placeholder, recognized by all validators (see {@link CValidator}),
 * CTypeValidator allows for the following placeholders to be specified:
 * <ul>
 * <li>{type}: replaced with data type the attribute should be {@link type}.</li>
 * </ul>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.validators
 * @since 1.0
 */
class CTypeValidator extends CValidator
{
	/**
	 * @var string the data type that the attribute should be. Defaults to 'string'.
	 * Valid values include 'string', 'integer', 'float', 'array', 'date', 'time' and 'datetime'.
	 */
	public $type='string';
	/**
	 * @var string the format pattern that the date value should follow. Defaults to 'MM/dd/yyyy'.
	 * Please see {@link CDateTimeParser} for details about how to specify a date format.
	 * This property is effective only when {@link type} is 'date'.
	 */
	public $dateFormat='MM/dd/yyyy';
	/**
	 * @var string the format pattern that the time value should follow. Defaults to 'hh:mm'.
	 * Please see {@link CDateTimeParser} for details about how to specify a time format.
	 * This property is effective only when {@link type} is 'time'.
	 */
	public $timeFormat='hh:mm';
	/**
	 * @var string the format pattern that the datetime value should follow. Defaults to 'MM/dd/yyyy hh:mm'.
	 * Please see {@link CDateTimeParser} for details about how to specify a datetime format.
	 * This property is effective only when {@link type} is 'datetime'.
	 */
	public $datetimeFormat='MM/dd/yyyy hh:mm';
	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty=true;

	/**
	 * @var boolean whether the actual PHP type of attribute value should be checked.
	 * Defaults to false, meaning that correctly formatted strings are accepted for
	 * integer and float validators.
	 *
	 * @since 1.1.13
	 */
	public $strict=false;

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	protected function validateAttribute($object,$attribute)
	{
		$value=$object->$attribute;
		if($this->allowEmpty && $this->isEmpty($value))
			return;

		if(!$this->validateValue($value))
		{
			$message=$this->message!==null?$this->message : Yii::t('yii','{attribute} must be {type}.');
			$this->addError($object,$attribute,$message,array('{type}'=>$this->type));
		}
	}

	/**
	 * Validates a static value.
	 * Note that this method does not respect {@link allowEmpty} property.
	 * This method is provided so that you can call it directly without going through the model validation rule mechanism.
	 * @param mixed $value the value to be validated
	 * @return boolean whether the value is valid
	 * @since 1.1.13
	 */
	public function validateValue($value)
	{
		$type=$this->type==='float' ? 'double' : $this->type;
		if($type===gettype($value))
			return true;
		elseif($this->strict || is_array($value) || is_object($value) || is_resource($value) || is_bool($value))
			return false;

		if($type==='integer')
			return (boolean)preg_match('/^[-+]?[0-9]+$/',trim($value));
		elseif($type==='double')
			return (boolean)preg_match('/^[-+]?([0-9]*\.)?[0-9]+([eE][-+]?[0-9]+)?$/',trim($value));
		elseif($type==='date')
			return CDateTimeParser::parse($value,$this->dateFormat,array('month'=>1,'day'=>1,'hour'=>0,'minute'=>0,'second'=>0))!==false;
		elseif($type==='time')
			return CDateTimeParser::parse($value,$this->timeFormat)!==false;
		elseif($type==='datetime')
			return CDateTimeParser::parse($value,$this->datetimeFormat, array('month'=>1,'day'=>1,'hour'=>0,'minute'=>0,'second'=>0))!==false;

		return false;
	}
}