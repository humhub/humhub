<?php
/**
 * CPropertyValue class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CPropertyValue is a helper class that provides static methods to convert component property values to specific types.
 *
 * CPropertyValue is commonly used in component setter methods to ensure
 * the new property value is of the specific type.
 * For example, a boolean-typed property setter method would be as follows,
 * <pre>
 * public function setPropertyName($value)
 * {
 *     $value=CPropertyValue::ensureBoolean($value);
 *     // $value is now of boolean type
 * }
 * </pre>
 *
 * Properties can be of the following types with specific type conversion rules:
 * <ul>
 * <li>string: a boolean value will be converted to 'true' or 'false'.</li>
 * <li>boolean: string 'true' (case-insensitive) will be converted to true,
 *            string 'false' (case-insensitive) will be converted to false.</li>
 * <li>integer</li>
 * <li>float</li>
 * <li>array: string starting with '(' and ending with ')' will be considered as
 *          as an array expression and will be evaluated. Otherwise, an array
 *          with the value to be ensured is returned.</li>
 * <li>object</li>
 * <li>enum: enumerable type, represented by an array of strings.</li>
 * </ul>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.utils
 * @since 1.0
 */
class CPropertyValue
{
	/**
	 * Converts a value to boolean type.
	 * Note, string 'true' (case-insensitive) will be converted to true,
	 * string 'false' (case-insensitive) will be converted to false.
	 * If a string represents a non-zero number, it will be treated as true.
	 * @param mixed $value the value to be converted.
	 * @return boolean
	 */
	public static function ensureBoolean($value)
	{
		if (is_string($value))
			return !strcasecmp($value,'true') || $value!=0;
		else
			return (boolean)$value;
	}

	/**
	 * Converts a value to string type.
	 * Note, a boolean value will be converted to 'true' if it is true
	 * and 'false' if it is false.
	 * @param mixed $value the value to be converted.
	 * @return string
	 */
	public static function ensureString($value)
	{
		if (is_bool($value))
			return $value?'true':'false';
		else
			return (string)$value;
	}

	/**
	 * Converts a value to integer type.
	 * @param mixed $value the value to be converted.
	 * @return integer
	 */
	public static function ensureInteger($value)
	{
		return (integer)$value;
	}

	/**
	 * Converts a value to float type.
	 * @param mixed $value the value to be converted.
	 * @return float
	 */
	public static function ensureFloat($value)
	{
		return (float)$value;
	}

	/**
	 * Converts a value to array type. If the value is a string and it is
	 * in the form (a,b,c) then an array consisting of each of the elements
	 * will be returned. If the value is a string and it is not in this form
	 * then an array consisting of just the string will be returned. If the value
	 * is not a string then
	 * @param mixed $value the value to be converted.
	 * @return array
	 */
	public static function ensureArray($value)
	{
		if(is_string($value))
		{
			$value = trim($value);
			$len = strlen($value);
			if ($len >= 2 && $value[0] == '(' && $value[$len-1] == ')')
			{
				eval('$array=array'.$value.';');
				return $array;
			}
			else
				return $len>0?array($value):array();
		}
		else
			return (array)$value;
	}

	/**
	 * Converts a value to object type.
	 * @param mixed $value the value to be converted.
	 * @return object
	 */
	public static function ensureObject($value)
	{
		return (object)$value;
	}

	/**
	 * Converts a value to enum type.
	 *
	 * This method checks if the value is of the specified enumerable type.
	 * A value is a valid enumerable value if it is equal to the name of a constant
	 * in the specified enumerable type (class).
	 * For more details about enumerable, see {@link CEnumerable}.
	 *
	 * @param string $value the enumerable value to be checked.
	 * @param string $enumType the enumerable class name (make sure it is included before calling this function).
	 * @return string the valid enumeration value
	 * @throws CException if the value is not a valid enumerable value
	 */
	public static function ensureEnum($value,$enumType)
	{
		static $types=array();
		if(!isset($types[$enumType]))
			$types[$enumType]=new ReflectionClass($enumType);
		if($types[$enumType]->hasConstant($value))
			return $value;
		else
			throw new CException(Yii::t('yii','Invalid enumerable value "{value}". Please make sure it is among ({enum}).',
				array('{value}'=>$value, '{enum}'=>implode(', ',$types[$enumType]->getConstants()))));
	}
}
