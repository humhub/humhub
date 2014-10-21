<?php
/**
 * CFormatter class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CFormatter provides a set of commonly used data formatting methods.
 *
 * The formatting methods provided by CFormatter are all named in the form of <code>formatXyz</code>.
 * The behavior of some of them may be configured via the properties of CFormatter. For example,
 * by configuring {@link dateFormat}, one may control how {@link formatDate} formats the value into a date string.
 *
 * For convenience, CFormatter also implements the mechanism of calling formatting methods with their shortcuts (called types).
 * In particular, if a formatting method is named <code>formatXyz</code>, then its shortcut method is <code>xyz</code>
 * (case-insensitive). For example, calling <code>$formatter->date($value)</code> is equivalent to calling
 * <code>$formatter->formatDate($value)</code>.
 *
 * Currently, the following types are recognizable:
 * <ul>
 * <li>raw: the attribute value will not be changed at all.</li>
 * <li>text: the attribute value will be HTML-encoded when rendering.</li>
 * <li>ntext: the {@link formatNtext} method will be called to format the attribute value as a HTML-encoded plain text with newlines converted as the HTML &lt;br /&gt; or &lt;p&gt;&lt;/p&gt; tags.</li>
 * <li>html: the attribute value will be purified and then returned.</li>
 * <li>date: the {@link formatDate} method will be called to format the attribute value as a date.</li>
 * <li>time: the {@link formatTime} method will be called to format the attribute value as a time.</li>
 * <li>datetime: the {@link formatDatetime} method will be called to format the attribute value as a date with time.</li>
 * <li>boolean: the {@link formatBoolean} method will be called to format the attribute value as a boolean display.</li>
 * <li>number: the {@link formatNumber} method will be called to format the attribute value as a number display.</li>
 * <li>email: the {@link formatEmail} method will be called to format the attribute value as a mailto link.</li>
 * <li>image: the {@link formatImage} method will be called to format the attribute value as an image tag where the attribute value is the image URL.</li>
 * <li>url: the {@link formatUrl} method will be called to format the attribute value as a hyperlink where the attribute value is the URL.</li>
 * <li>size: the {@link formatSize} method will be called to format the attribute value, interpreted as a number of bytes, as a size in human readable form.</li>
 * </ul>
 *
 * By default, {@link CApplication} registers {@link CFormatter} as an application component whose ID is 'format'.
 * Therefore, one may call <code>Yii::app()->format->boolean(1)</code>.
 * You might want to replace this component with {@link CLocalizedFormatter} to enable formatting based on the
 * current locale settings.
 *
 * @property CHtmlPurifier $htmlPurifier The HTML purifier instance.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.utils
 * @since 1.1.0
 */
class CFormatter extends CApplicationComponent
{
	/**
	 * @var CHtmlPurifier
	 */
	private $_htmlPurifier;

	/**
	 * @var string the format string to be used to format a date using PHP date() function. Defaults to 'Y/m/d'.
	 */
	public $dateFormat='Y/m/d';
	/**
	 * @var string the format string to be used to format a time using PHP date() function. Defaults to 'h:i:s A'.
	 */
	public $timeFormat='h:i:s A';
	/**
	 * @var string the format string to be used to format a date and time using PHP date() function. Defaults to 'Y/m/d h:i:s A'.
	 */
	public $datetimeFormat='Y/m/d h:i:s A';
	/**
	 * @var array the format used to format a number with PHP number_format() function.
	 * Three elements may be specified: "decimals", "decimalSeparator" and "thousandSeparator".
	 * They correspond to the number of digits after the decimal point, the character displayed as the decimal point
	 * and the thousands separator character.
	 */
	public $numberFormat=array('decimals'=>null, 'decimalSeparator'=>null, 'thousandSeparator'=>null);
	/**
	 * @var array the text to be displayed when formatting a boolean value. The first element corresponds
	 * to the text display for false, the second element for true. Defaults to <code>array('No', 'Yes')</code>.
	 */
	public $booleanFormat=array('No','Yes');
	/**
	 * @var array the options to be passed to CHtmlPurifier instance used in this class. CHtmlPurifier is used
	 * in {@link formatHtml} method, so this property could be useful to customize HTML filtering behavior.
	 * @since 1.1.13
	 */
	public $htmlPurifierOptions=array();
	/**
	 * @var array the format used to format size (bytes). Three elements may be specified: "base", "decimals" and "decimalSeparator".
	 * They correspond to the base at which a kilobyte is calculated (1000 or 1024 bytes per kilobyte, defaults to 1024),
	 * the number of digits after the decimal point (defaults to 2) and the character displayed as the decimal point.
	 * "decimalSeparator" is available since version 1.1.13
	 * @since 1.1.11
	 */
	public $sizeFormat=array(
		'base'=>1024,
		'decimals'=>2,
		'decimalSeparator'=>null,
	);

	/**
	 * Calls the format method when its shortcut is invoked.
	 * This is a PHP magic method that we override to implement the shortcut format methods.
	 * @param string $name the method name
	 * @param array $parameters method parameters
	 * @return mixed the method return value
	 */
	public function __call($name,$parameters)
	{
		if(method_exists($this,'format'.$name))
			return call_user_func_array(array($this,'format'.$name),$parameters);
		else
			return parent::__call($name,$parameters);
	}

	/**
	 * Formats a value based on the given type.
	 * @param mixed $value the value to be formatted
	 * @param string $type the data type. This must correspond to a format method available in CFormatter.
	 * For example, we can use 'text' here because there is method named {@link formatText}.
	 * @throws CException if given type is unknown
	 * @return string the formatted data
	 */
	public function format($value,$type)
	{
		$method='format'.$type;
		if(method_exists($this,$method))
			return $this->$method($value);
		else
			throw new CException(Yii::t('yii','Unknown type "{type}".',array('{type}'=>$type)));
	}

	/**
	 * Formats the value as is without any formatting.
	 * This method simply returns back the parameter without any format.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 */
	public function formatRaw($value)
	{
		return $value;
	}

	/**
	 * Formats the value as a HTML-encoded plain text.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 */
	public function formatText($value)
	{
		return CHtml::encode($value);
	}

	/**
	 * Formats the value as a HTML-encoded plain text and converts newlines with HTML &lt;br /&gt; or
	 * &lt;p&gt;&lt;/p&gt; tags.
	 * @param mixed $value the value to be formatted
	 * @param boolean $paragraphs whether newlines should be converted to HTML &lt;p&gt;&lt;/p&gt; tags,
	 * false by default meaning that HTML &lt;br /&gt; tags will be used
	 * @param boolean $removeEmptyParagraphs whether empty paragraphs should be removed, defaults to true;
	 * makes sense only when $paragraphs parameter is true
	 * @return string the formatted result
	 */
	public function formatNtext($value,$paragraphs=false,$removeEmptyParagraphs=true)
	{
		$value=CHtml::encode($value);
		if($paragraphs)
		{
			$value='<p>'.str_replace(array("\r\n", "\n", "\r"), '</p><p>',$value).'</p>';
			if($removeEmptyParagraphs)
     			$value=preg_replace('/(<\/p><p>){2,}/i','</p><p>',$value);
			return $value;
		}
		else
		{
			return nl2br($value);
		}
	}

	/**
	 * Formats the value as HTML text without any encoding.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 */
	public function formatHtml($value)
	{
		return $this->getHtmlPurifier()->purify($value);
	}

	/**
	 * Formats the value as a date.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 * @see dateFormat
	 */
	public function formatDate($value)
	{
		return date($this->dateFormat,$this->normalizeDateValue($value));
	}

	/**
	 * Formats the value as a time.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 * @see timeFormat
	 */
	public function formatTime($value)
	{
		return date($this->timeFormat,$this->normalizeDateValue($value));
	}

	/**
	 * Formats the value as a date and time.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 * @see datetimeFormat
	 */
	public function formatDatetime($value)
	{
		return date($this->datetimeFormat,$this->normalizeDateValue($value));
	}

	/**
	 * Normalizes an expression as a timestamp.
	 * @param mixed $time the time expression to be normalized
	 * @return int the normalized result as a UNIX timestamp
	 */
	protected function normalizeDateValue($time)
	{
		if(is_string($time))
		{
			if(ctype_digit($time) || ($time{0}=='-' && ctype_digit(substr($time, 1))))
				return (int)$time;
			else
				return strtotime($time);
		}
		return (int)$time;
	}

	/**
	 * Formats the value as a boolean.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 * @see booleanFormat
	 */
	public function formatBoolean($value)
	{
		return $value ? $this->booleanFormat[1] : $this->booleanFormat[0];
	}

	/**
	 * Formats the value as a mailto link.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 */
	public function formatEmail($value)
	{
		return CHtml::mailto($value);
	}

	/**
	 * Formats the value as an image tag.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 */
	public function formatImage($value)
	{
		return CHtml::image($value);
	}

	/**
	 * Formats the value as a hyperlink.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 */
	public function formatUrl($value)
	{
		$url=$value;
		if(strpos($url,'http://')!==0 && strpos($url,'https://')!==0)
			$url='http://'.$url;
		return CHtml::link(CHtml::encode($value),$url);
	}

	/**
	 * Formats the value as a number using PHP number_format() function.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 * @see numberFormat
	 */
	public function formatNumber($value)
	{
		return number_format($value,$this->numberFormat['decimals'],$this->numberFormat['decimalSeparator'],$this->numberFormat['thousandSeparator']);
	}

	/**
	 * @return CHtmlPurifier the HTML purifier instance
	 */
	public function getHtmlPurifier()
	{
		if($this->_htmlPurifier===null)
			$this->_htmlPurifier=new CHtmlPurifier;
		$this->_htmlPurifier->options=$this->htmlPurifierOptions;
		return $this->_htmlPurifier;
	}

	/**
	 * Formats the value in bytes as a size in human readable form.
	 * @param integer $value value in bytes to be formatted
	 * @param boolean $verbose if full names should be used (e.g. bytes, kilobytes, ...).
	 * Defaults to false meaning that short names will be used (e.g. B, KB, ...).
	 * @return string the formatted result
	 * @see sizeFormat
	 * @since 1.1.11
	 */
	public function formatSize($value,$verbose=false)
	{
		$base=$this->sizeFormat['base'];
		for($i=0; $base<=$value && $i<5; $i++)
			$value=$value/$base;

		$value=round($value, $this->sizeFormat['decimals']);
		$formattedValue=isset($this->sizeFormat['decimalSeparator']) ? str_replace('.',$this->sizeFormat['decimalSeparator'],$value) : $value;
		$params=array($value,'{n}'=>$formattedValue);

		switch($i)
		{
			case 0:
				return $verbose ? Yii::t('yii','{n} byte|{n} bytes',$params) : Yii::t('yii', '{n} B',$params);
			case 1:
				return $verbose ? Yii::t('yii','{n} kilobyte|{n} kilobytes',$params) : Yii::t('yii','{n} KB',$params);
			case 2:
				return $verbose ? Yii::t('yii','{n} megabyte|{n} megabytes',$params) : Yii::t('yii','{n} MB',$params);
			case 3:
				return $verbose ? Yii::t('yii','{n} gigabyte|{n} gigabytes',$params) : Yii::t('yii','{n} GB',$params);
			default:
				return $verbose ? Yii::t('yii','{n} terabyte|{n} terabytes',$params) : Yii::t('yii','{n} TB',$params);
		}
	}
}
