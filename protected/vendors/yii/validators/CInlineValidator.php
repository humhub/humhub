<?php
/**
 * CInlineValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CInlineValidator represents a validator which is defined as a method in the object being validated.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.validators
 * @since 1.0
 */
class CInlineValidator extends CValidator
{
	/**
	 * @var string the name of the validation method defined in the active record class
	 */
	public $method;
	/**
	 * @var array additional parameters that are passed to the validation method
	 */
	public $params;
	/**
	 * @var string the name of the method that returns the client validation code (See {@link clientValidateAttribute}).
	 */
	public $clientValidate;

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	protected function validateAttribute($object,$attribute)
	{
		$method=$this->method;
		$object->$method($attribute,$this->params);
	}

	/**
	 * Returns the JavaScript code needed to perform client-side validation by calling the {@link clientValidate} method.
	 * In the client validation code, these variables are predefined:
	 * <ul>
	 * <li>value: the current input value associated with this attribute.</li>
	 * <li>messages: an array that may be appended with new error messages for the attribute.</li>
	 * <li>attribute: a data structure keeping all client-side options for the attribute</li>
	 * </ul>
	 * <b>Example</b>:
	 *
	 * If {@link clientValidate} is set to "clientValidate123", clientValidate123() is the name of
	 * the method that returns the client validation code and can look like:
	 * <pre>
	 * <?php
	 *   public function clientValidate123($attribute,$params)
	 *   {
	 *      if(!isset($params['message']))
	 *         $params['message']='Value should be 123';
	 *      $js = "if(value != '123') { messages.push($params['message']); }";
	 *      return $js;
	 *   }
	 * ?>
	 * </pre>
	 * @param CModel $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 * @return string the client-side validation script.
	 * @see CActiveForm::enableClientValidation
	 * @since 1.1.9
	 */
	public function clientValidateAttribute($object,$attribute)
	{
		if($this->clientValidate!==null)
		{
			$method=$this->clientValidate;
			return $object->$method($attribute,$this->params);
		}
	}
}
