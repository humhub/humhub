<?php
/**
 * CNumberFormatter class file.
 *
 * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CNumberFormatter provides number localization functionalities.
 *
 * CNumberFormatter formats a number (integer or float) and outputs a string
 * based on the specified format. A CNumberFormatter instance is associated with a locale,
 * and thus generates the string representation of the number in a locale-dependent fashion.
 *
 * CNumberFormatter currently supports currency format, percentage format, decimal format,
 * and custom format. The first three formats are specified in the locale data, while the custom
 * format allows you to enter an arbitrary format string.
 *
 * A format string may consist of the following special characters:
 * <ul>
 * <li>dot (.): the decimal point. It will be replaced with the localized decimal point.</li>
 * <li>comma (,): the grouping separator. It will be replaced with the localized grouping separator.</li>
 * <li>zero (0): required digit. This specifies the places where a digit must appear (will pad 0 if not).</li>
 * <li>hash (#): optional digit. This is mainly used to specify the location of decimal point and grouping separators.</li>
 * <li>currency (¤): the currency placeholder. It will be replaced with the localized currency symbol.</li>
 * <li>percentage (%): the percentage mark. If appearing, the number will be multiplied by 100 before being formatted.</li>
 * <li>permillage (‰): the permillage mark. If appearing, the number will be multiplied by 1000 before being formatted.</li>
 * <li>semicolon (;): the character separating positive and negative number sub-patterns.</li>
 * </ul>
 *
 * Anything surrounding the pattern (or sub-patterns) will be kept.
 *
 * The followings are some examples:
 * <pre>
 * Pattern "#,##0.00" will format 12345.678 as "12,345.68".
 * Pattern "#,#,#0.00" will format 12345.6 as "1,2,3,45.60".
 * </pre>
 * Note, in the first example, the number is rounded first before applying the formatting.
 * And in the second example, the pattern specifies two grouping sizes.
 *
 * CNumberFormatter attempts to implement number formatting according to
 * the {@link http://www.unicode.org/reports/tr35/ Unicode Technical Standard #35}.
 * The following features are NOT implemented:
 * <ul>
 * <li>significant digit</li>
 * <li>scientific format</li>
 * <li>arbitrary literal characters</li>
 * <li>arbitrary padding</li>
 * </ul>
 *
 * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.i18n
 * @since 1.0
 */
class CNumberFormatter extends CComponent
{
	private $_locale;
	private $_formats=array();

	/**
	 * Constructor.
	 * @param mixed $locale locale ID (string) or CLocale instance
	 */
	public function __construct($locale)
	{
		if(is_string($locale))
			$this->_locale=CLocale::getInstance($locale);
		else
			$this->_locale=$locale;
	}

	/**
	 * Formats a number based on the specified pattern.
	 * Note, if the format contains '%', the number will be multiplied by 100 first.
	 * If the format contains '‰', the number will be multiplied by 1000.
	 * If the format contains currency placeholder, it will be replaced by
	 * the specified localized currency symbol.
	 * @param string $pattern format pattern
	 * @param mixed $value the number to be formatted
	 * @param string $currency 3-letter ISO 4217 code. For example, the code "USD" represents the US Dollar and "EUR" represents the Euro currency.
	 * The currency placeholder in the pattern will be replaced with the currency symbol.
	 * If null, no replacement will be done.
	 * @return string the formatting result.
	 */
	public function format($pattern,$value,$currency=null)
	{
		$format=$this->parseFormat($pattern);
		$result=$this->formatNumber($format,$value);
		if($currency===null)
			return $result;
		elseif(($symbol=$this->_locale->getCurrencySymbol($currency))===null)
			$symbol=$currency;
		return str_replace('¤',$symbol,$result);
	}

	/**
	 * Formats a number using the currency format defined in the locale.
	 * @param mixed $value the number to be formatted
	 * @param string $currency 3-letter ISO 4217 code. For example, the code "USD" represents the US Dollar and "EUR" represents the Euro currency.
	 * The currency placeholder in the pattern will be replaced with the currency symbol.
	 * @return string the formatting result.
	 */
	public function formatCurrency($value,$currency)
	{
		return $this->format($this->_locale->getCurrencyFormat(),$value,$currency);
	}

	/**
	 * Formats a number using the percentage format defined in the locale.
	 * Note, if the percentage format contains '%', the number will be multiplied by 100 first.
	 * If the percentage format contains '‰', the number will be multiplied by 1000.
	 * @param mixed $value the number to be formatted
	 * @return string the formatting result.
	 */
	public function formatPercentage($value)
	{
		return $this->format($this->_locale->getPercentFormat(),$value);
	}

	/**
	 * Formats a number using the decimal format defined in the locale.
	 * @param mixed $value the number to be formatted
	 * @return string the formatting result.
	 */
	public function formatDecimal($value)
	{
		return $this->format($this->_locale->getDecimalFormat(),$value);
	}

	/**
	 * Formats a number based on a format.
	 * This is the method that does actual number formatting.
	 * @param array $format format with the following structure:
	 * <pre>
	 * array(
	 * 	// number of required digits after the decimal point,
	 * 	// will be padded with 0 if not enough digits,
	 * 	// -1 means we should drop the decimal point
	 * 	'decimalDigits'=>2,
	 * 	// maximum number of digits after the decimal point,
	 * 	// additional digits will be truncated.
	 * 	'maxDecimalDigits'=>3,
	 * 	// number of required digits before the decimal point,
	 * 	// will be padded with 0 if not enough digits
	 * 	'integerDigits'=>1,
	 * 	// the primary grouping size, 0 means no grouping
	 * 	'groupSize1'=>3,
	 * 	// the secondary grouping size, 0 means no secondary grouping
	 * 	'groupSize2'=>0,
	 * 	'positivePrefix'=>'+',  // prefix to positive number
	 * 	'positiveSuffix'=>'',   // suffix to positive number
	 * 	'negativePrefix'=>'(',  // prefix to negative number
	 * 	'negativeSuffix'=>')',  // suffix to negative number
	 * 	'multiplier'=>1,        // 100 for percent, 1000 for per mille
	 * );
	 * </pre>
	 * @param mixed $value the number to be formatted
	 * @return string the formatted result
	 */
	protected function formatNumber($format,$value)
	{
		$negative=$value<0;
		$value=abs($value*$format['multiplier']);
		if($format['maxDecimalDigits']>=0)
			$value=number_format($value,$format['maxDecimalDigits'],'.','');
		$value="$value";
		if(false !== $pos=strpos($value,'.'))
		{
			$integer=substr($value,0,$pos);
			$decimal=substr($value,$pos+1);
		}
		else
		{
			$integer=$value;
			$decimal='';
		}
		if($format['decimalDigits']>strlen($decimal))
			$decimal=str_pad($decimal,$format['decimalDigits'],'0');
		elseif($format['decimalDigits']<strlen($decimal))
		{
			$decimal_temp='';
			for($i=strlen($decimal)-1;$i>=0;$i--)
				if($decimal[$i]!=='0' || strlen($decimal_temp)>0)
					$decimal_temp=$decimal[$i].$decimal_temp;
			$decimal=$decimal_temp;
		}
		if(strlen($decimal)>0)
			$decimal=$this->_locale->getNumberSymbol('decimal').$decimal;

		$integer=str_pad($integer,$format['integerDigits'],'0',STR_PAD_LEFT);
		if($format['groupSize1']>0 && strlen($integer)>$format['groupSize1'])
		{
			$str1=substr($integer,0,-$format['groupSize1']);
			$str2=substr($integer,-$format['groupSize1']);
			$size=$format['groupSize2']>0?$format['groupSize2']:$format['groupSize1'];
			$str1=str_pad($str1,(int)((strlen($str1)+$size-1)/$size)*$size,' ',STR_PAD_LEFT);
			$integer=ltrim(implode($this->_locale->getNumberSymbol('group'),str_split($str1,$size))).$this->_locale->getNumberSymbol('group').$str2;
		}

		if($negative)
			$number=$format['negativePrefix'].$integer.$decimal.$format['negativeSuffix'];
		else
			$number=$format['positivePrefix'].$integer.$decimal.$format['positiveSuffix'];

		return strtr($number,array('%'=>$this->_locale->getNumberSymbol('percentSign'),'‰'=>$this->_locale->getNumberSymbol('perMille')));
	}

	/**
	 * Parses a given string pattern.
	 * @param string $pattern the pattern to be parsed
	 * @return array the parsed pattern
	 * @see formatNumber
	 */
	protected function parseFormat($pattern)
	{
		if(isset($this->_formats[$pattern]))
			return $this->_formats[$pattern];

		$format=array();

		// find out prefix and suffix for positive and negative patterns
		$patterns=explode(';',$pattern);
		$format['positivePrefix']=$format['positiveSuffix']=$format['negativePrefix']=$format['negativeSuffix']='';
		if(preg_match('/^(.*?)[#,\.0]+(.*?)$/',$patterns[0],$matches))
		{
			$format['positivePrefix']=$matches[1];
			$format['positiveSuffix']=$matches[2];
		}

		if(isset($patterns[1]) && preg_match('/^(.*?)[#,\.0]+(.*?)$/',$patterns[1],$matches))  // with a negative pattern
		{
			$format['negativePrefix']=$matches[1];
			$format['negativeSuffix']=$matches[2];
		}
		else
		{
			$format['negativePrefix']=$this->_locale->getNumberSymbol('minusSign').$format['positivePrefix'];
			$format['negativeSuffix']=$format['positiveSuffix'];
		}
		$pat=$patterns[0];

		// find out multiplier
		if(strpos($pat,'%')!==false)
			$format['multiplier']=100;
		elseif(strpos($pat,'‰')!==false)
			$format['multiplier']=1000;
		else
			$format['multiplier']=1;

		// find out things about decimal part
		if(($pos=strpos($pat,'.'))!==false)
		{
			if(($pos2=strrpos($pat,'0'))>$pos)
				$format['decimalDigits']=$pos2-$pos;
			else
				$format['decimalDigits']=0;
			if(($pos3=strrpos($pat,'#'))>=$pos2)
				$format['maxDecimalDigits']=$pos3-$pos;
			else
				$format['maxDecimalDigits']=$format['decimalDigits'];
			$pat=substr($pat,0,$pos);
		}
		else   // no decimal part
		{
			$format['decimalDigits']=0;
			$format['maxDecimalDigits']=0;
		}

		// find out things about integer part
		$p=str_replace(',','',$pat);
		if(($pos=strpos($p,'0'))!==false)
			$format['integerDigits']=strrpos($p,'0')-$pos+1;
		else
			$format['integerDigits']=0;
		// find out group sizes. some patterns may have two different group sizes
		$p=str_replace('#','0',$pat);
		if(($pos=strrpos($pat,','))!==false)
		{
			$format['groupSize1']=strrpos($p,'0')-$pos;
			if(($pos2=strrpos(substr($p,0,$pos),','))!==false)
				$format['groupSize2']=$pos-$pos2-1;
			else
				$format['groupSize2']=0;
		}
		else
			$format['groupSize1']=$format['groupSize2']=0;

		return $this->_formats[$pattern]=$format;
	}
}