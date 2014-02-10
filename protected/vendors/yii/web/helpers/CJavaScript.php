<?php
/**
 * CJavaScript helper class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CJavaScript is a helper class containing JavaScript-related handling functions.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.helpers
 * @since 1.0
 */
class CJavaScript
{
	/**
	 * Quotes a javascript string.
	 * After processing, the string can be safely enclosed within a pair of
	 * quotation marks and serve as a javascript string.
	 * @param string $js string to be quoted
	 * @param boolean $forUrl whether this string is used as a URL
	 * @return string the quoted string
	 */
	public static function quote($js,$forUrl=false)
	{
		if($forUrl)
			return strtr($js,array('%'=>'%25',"\t"=>'\t',"\n"=>'\n',"\r"=>'\r','"'=>'\"','\''=>'\\\'','\\'=>'\\\\','</'=>'<\/'));
		else
			return strtr($js,array("\t"=>'\t',"\n"=>'\n',"\r"=>'\r','"'=>'\"','\''=>'\\\'','\\'=>'\\\\','</'=>'<\/'));
	}

	/**
	 * Encodes a PHP variable into javascript representation.
	 *
	 * Example:
	 * <pre>
	 * $options=array('key1'=>true,'key2'=>123,'key3'=>'value');
	 * echo CJavaScript::encode($options);
	 * // The following javascript code would be generated:
	 * // {'key1':true,'key2':123,'key3':'value'}
	 * </pre>
	 *
	 * For highly complex data structures use {@link jsonEncode} and {@link jsonDecode}
	 * to serialize and unserialize.
	 *
	 * If you are encoding user input, make sure $safe is set to true.
	 *
	 * @param mixed $value PHP variable to be encoded
	 * @param boolean $safe If true, 'js:' will not be allowed. In case of
	 * wrapping code with {@link CJavaScriptExpression} JavaScript expression
	 * will stay as is no matter what value this parameter is set to.
	 * Default is false. This parameter is available since 1.1.11.
	 * @return string the encoded string
	 */
	public static function encode($value,$safe=false)
	{
		if(is_string($value))
		{
			if(strpos($value,'js:')===0 && $safe===false)
				return substr($value,3);
			else
				return "'".self::quote($value)."'";
		}
		elseif($value===null)
			return 'null';
		elseif(is_bool($value))
			return $value?'true':'false';
		elseif(is_integer($value))
			return "$value";
		elseif(is_float($value))
		{
			if($value===-INF)
				return 'Number.NEGATIVE_INFINITY';
			elseif($value===INF)
				return 'Number.POSITIVE_INFINITY';
			else
				return rtrim(sprintf('%.16F',$value),'0');  // locale-independent representation
		}
		elseif($value instanceof CJavaScriptExpression)
			return $value->__toString();
		elseif(is_object($value))
			return self::encode(get_object_vars($value),$safe);
		elseif(is_array($value))
		{
			$es=array();
			if(($n=count($value))>0 && array_keys($value)!==range(0,$n-1))
			{
				foreach($value as $k=>$v)
					$es[]="'".self::quote($k)."':".self::encode($v,$safe);
				return '{'.implode(',',$es).'}';
			}
			else
			{
				foreach($value as $v)
					$es[]=self::encode($v,$safe);
				return '['.implode(',',$es).']';
			}
		}
		else
			return '';
	}

	/**
	 * Returns the JSON representation of the PHP data.
	 * @param mixed $data the data to be encoded
	 * @return string the JSON representation of the PHP data.
	 */
	public static function jsonEncode($data)
	{
		return CJSON::encode($data);
	}

	/**
	 * Decodes a JSON string.
	 * @param string $data the data to be decoded
	 * @param boolean $useArray whether to use associative array to represent object data
	 * @return mixed the decoded PHP data
	 */
	public static function jsonDecode($data,$useArray=true)
	{
		return CJSON::decode($data,$useArray);
	}
}
