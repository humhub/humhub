<?php
/**
 * CDefaultValueValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDefaultValueValidator sets the attributes with the specified value.
 * It does not do validation but rather allows setting a default value at the
 * same time validation is performed. Usually this happens when calling either
 * <code>$model->validate()</code> or <code>$model->save()</code>.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.validators
 */
class CDefaultValueValidator extends CValidator
{
	/**
	 * @var mixed the default value to be set to the specified attributes.
	 */
	public $value;
	/**
	 * @var boolean whether to set the default value only when the attribute value is null or empty string.
	 * Defaults to true. If false, the attribute will always be assigned with the default value,
	 * even if it is already explicitly assigned a value.
	 */
	public $setOnEmpty=true;

	/**
	 * Validates the attribute of the object.
	 * @param CModel $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	protected function validateAttribute($object,$attribute)
	{
		if(!$this->setOnEmpty)
			$object->$attribute=$this->value;
		else
		{
			$value=$object->$attribute;
			if($value===null || $value==='')
				$object->$attribute=$this->value;
		}
	}
}

