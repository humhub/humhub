<?php
/**
 * CLocale class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CLocale represents the data relevant to a locale.
 *
 * The data includes the number formatting information and date formatting information.
 *
 * @property string $id The locale ID (in canonical form).
 * @property CNumberFormatter $numberFormatter The number formatter for this locale.
 * @property CDateFormatter $dateFormatter The date formatter for this locale.
 * @property string $decimalFormat The decimal format.
 * @property string $currencyFormat The currency format.
 * @property string $percentFormat The percent format.
 * @property string $scientificFormat The scientific format.
 * @property array $monthNames Month names indexed by month values (1-12).
 * @property array $weekDayNames The weekday names indexed by weekday values (0-6, 0 means Sunday, 1 Monday, etc.).
 * @property string $aMName The AM name.
 * @property string $pMName The PM name.
 * @property string $dateFormat Date format.
 * @property string $timeFormat Date format.
 * @property string $dateTimeFormat Datetime format, i.e., the order of date and time.
 * @property string $orientation The character orientation, which is either 'ltr' (left-to-right) or 'rtl' (right-to-left).
 * @property array $pluralRules Plural forms expressions.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.i18n
 * @since 1.0
 */
class CLocale extends CComponent
{
	/**
	 * @var string the directory that contains the locale data. If this property is not set,
	 * the locale data will be loaded from 'framework/i18n/data'.
	 * @since 1.1.0
	 */
	public static $dataPath;

	private $_id;
	private $_data;
	private $_dateFormatter;
	private $_numberFormatter;

	/**
	 * Returns the instance of the specified locale.
	 * Since the constructor of CLocale is protected, you can only use
	 * this method to obtain an instance of the specified locale.
	 * @param string $id the locale ID (e.g. en_US)
	 * @return CLocale the locale instance
	 */
	public static function getInstance($id)
	{
		static $locales=array();
		if(isset($locales[$id]))
			return $locales[$id];
		else
			return $locales[$id]=new CLocale($id);
	}

	/**
	 * @return array IDs of the locales which the framework can recognize
	 */
	public static function getLocaleIDs()
	{
		static $locales;
		if($locales===null)
		{
			$locales=array();
			$dataPath=self::$dataPath===null ? dirname(__FILE__).DIRECTORY_SEPARATOR.'data' : self::$dataPath;
			$folder=@opendir($dataPath);
			while(($file=@readdir($folder))!==false)
			{
				$fullPath=$dataPath.DIRECTORY_SEPARATOR.$file;
				if(substr($file,-4)==='.php' && is_file($fullPath))
					$locales[]=substr($file,0,-4);
			}
			closedir($folder);
			sort($locales);
		}
		return $locales;
	}

	/**
	 * Constructor.
	 * Since the constructor is protected, please use {@link getInstance}
	 * to obtain an instance of the specified locale.
	 * @param string $id the locale ID (e.g. en_US)
	 */
	protected function __construct($id)
	{
		$this->_id=self::getCanonicalID($id);
		$dataPath=self::$dataPath===null ? dirname(__FILE__).DIRECTORY_SEPARATOR.'data' : self::$dataPath;
		$dataFile=$dataPath.DIRECTORY_SEPARATOR.$this->_id.'.php';
		if(is_file($dataFile))
			$this->_data=require($dataFile);
		else
			throw new CException(Yii::t('yii','Unrecognized locale "{locale}".',array('{locale}'=>$id)));
	}

	/**
	 * Converts a locale ID to its canonical form.
	 * In canonical form, a locale ID consists of only underscores and lower-case letters.
	 * @param string $id the locale ID to be converted
	 * @return string the locale ID in canonical form
	 */
	public static function getCanonicalID($id)
	{
		return strtolower(str_replace('-','_',$id));
	}

	/**
	 * @return string the locale ID (in canonical form)
	 */
	public function getId()
	{
		return $this->_id;
	}

	/**
	 * @return CNumberFormatter the number formatter for this locale
	 */
	public function getNumberFormatter()
	{
		if($this->_numberFormatter===null)
			$this->_numberFormatter=new CNumberFormatter($this);
		return $this->_numberFormatter;
	}

	/**
	 * @return CDateFormatter the date formatter for this locale
	 */
	public function getDateFormatter()
	{
		if($this->_dateFormatter===null)
			$this->_dateFormatter=new CDateFormatter($this);
		return $this->_dateFormatter;
	}

	/**
	 * @param string $currency 3-letter ISO 4217 code. For example, the code "USD" represents the US Dollar and "EUR" represents the Euro currency.
	 * @return string the localized currency symbol. Null if the symbol does not exist.
	 */
	public function getCurrencySymbol($currency)
	{
		return isset($this->_data['currencySymbols'][$currency]) ? $this->_data['currencySymbols'][$currency] : null;
	}

	/**
	 * @param string $name symbol name
	 * @return string symbol
	 */
	public function getNumberSymbol($name)
	{
		return isset($this->_data['numberSymbols'][$name]) ? $this->_data['numberSymbols'][$name] : null;
	}

	/**
	 * @return string the decimal format
	 */
	public function getDecimalFormat()
	{
		return $this->_data['decimalFormat'];
	}

	/**
	 * @return string the currency format
	 */
	public function getCurrencyFormat()
	{
		return $this->_data['currencyFormat'];
	}

	/**
	 * @return string the percent format
	 */
	public function getPercentFormat()
	{
		return $this->_data['percentFormat'];
	}

	/**
	 * @return string the scientific format
	 */
	public function getScientificFormat()
	{
		return $this->_data['scientificFormat'];
	}

	/**
	 * @param integer $month month (1-12)
	 * @param string $width month name width. It can be 'wide', 'abbreviated' or 'narrow'.
	 * @param boolean $standAlone whether the month name should be returned in stand-alone format
	 * @return string the month name
	 */
	public function getMonthName($month,$width='wide',$standAlone=false)
	{
		if($standAlone)
			return isset($this->_data['monthNamesSA'][$width][$month]) ? $this->_data['monthNamesSA'][$width][$month] : $this->_data['monthNames'][$width][$month];
		else
			return isset($this->_data['monthNames'][$width][$month]) ? $this->_data['monthNames'][$width][$month] : $this->_data['monthNamesSA'][$width][$month];
	}

	/**
	 * Returns the month names in the specified width.
	 * @param string $width month name width. It can be 'wide', 'abbreviated' or 'narrow'.
	 * @param boolean $standAlone whether the month names should be returned in stand-alone format
	 * @return array month names indexed by month values (1-12)
	 */
	public function getMonthNames($width='wide',$standAlone=false)
	{
		if($standAlone)
			return isset($this->_data['monthNamesSA'][$width]) ? $this->_data['monthNamesSA'][$width] : $this->_data['monthNames'][$width];
		else
			return isset($this->_data['monthNames'][$width]) ? $this->_data['monthNames'][$width] : $this->_data['monthNamesSA'][$width];
	}

	/**
	 * @param integer $day weekday (0-7, 0 and 7 means Sunday)
	 * @param string $width weekday name width.  It can be 'wide', 'abbreviated' or 'narrow'.
	 * @param boolean $standAlone whether the week day name should be returned in stand-alone format
	 * @return string the weekday name
	 */
	public function getWeekDayName($day,$width='wide',$standAlone=false)
	{
		$day=$day%7;
		if($standAlone)
			return isset($this->_data['weekDayNamesSA'][$width][$day]) ? $this->_data['weekDayNamesSA'][$width][$day] : $this->_data['weekDayNames'][$width][$day];
		else
			return isset($this->_data['weekDayNames'][$width][$day]) ? $this->_data['weekDayNames'][$width][$day] : $this->_data['weekDayNamesSA'][$width][$day];
	}

	/**
	 * Returns the week day names in the specified width.
	 * @param string $width weekday name width.  It can be 'wide', 'abbreviated' or 'narrow'.
	 * @param boolean $standAlone whether the week day name should be returned in stand-alone format
	 * @return array the weekday names indexed by weekday values (0-6, 0 means Sunday, 1 Monday, etc.)
	 */
	public function getWeekDayNames($width='wide',$standAlone=false)
	{
		if($standAlone)
			return isset($this->_data['weekDayNamesSA'][$width]) ? $this->_data['weekDayNamesSA'][$width] : $this->_data['weekDayNames'][$width];
		else
			return isset($this->_data['weekDayNames'][$width]) ? $this->_data['weekDayNames'][$width] : $this->_data['weekDayNamesSA'][$width];
	}

	/**
	 * @param integer $era era (0,1)
	 * @param string $width era name width.  It can be 'wide', 'abbreviated' or 'narrow'.
	 * @return string the era name
	 */
	public function getEraName($era,$width='wide')
	{
		return $this->_data['eraNames'][$width][$era];
	}

	/**
	 * @return string the AM name
	 */
	public function getAMName()
	{
		return $this->_data['amName'];
	}

	/**
	 * @return string the PM name
	 */
	public function getPMName()
	{
		return $this->_data['pmName'];
	}

	/**
	 * @param string $width date format width. It can be 'full', 'long', 'medium' or 'short'.
	 * @return string date format
	 */
	public function getDateFormat($width='medium')
	{
		return $this->_data['dateFormats'][$width];
	}

	/**
	 * @param string $width time format width. It can be 'full', 'long', 'medium' or 'short'.
	 * @return string date format
	 */
	public function getTimeFormat($width='medium')
	{
		return $this->_data['timeFormats'][$width];
	}

	/**
	 * @return string datetime format, i.e., the order of date and time.
	 */
	public function getDateTimeFormat()
	{
		return $this->_data['dateTimeFormat'];
	}

	/**
	 * @return string the character orientation, which is either 'ltr' (left-to-right) or 'rtl' (right-to-left)
	 * @since 1.1.2
	 */
	public function getOrientation()
	{
		return isset($this->_data['orientation']) ? $this->_data['orientation'] : 'ltr';
	}

	/**
	 * @return array plural forms expressions
	 */
	public function getPluralRules()
	{
		return isset($this->_data['pluralRules']) ? $this->_data['pluralRules'] : array();
	}

	/**
	 * Converts a locale ID to a language ID.
	 * A language ID consists of only the first group of letters before an underscore or dash.
	 * @param string $id the locale ID to be converted
	 * @return string the language ID
	 * @since 1.1.9
	 */
	public function getLanguageID($id)
	{
		// normalize id
		$id = $this->getCanonicalID($id);
		// remove sub tags
		if(($underscorePosition=strpos($id, '_'))!== false)
		{
			$id = substr($id, 0, $underscorePosition);
		}
		return $id;
	}

	/**
	 * Converts a locale ID to a script ID.
	 * A script ID consists of only the last four characters after an underscore or dash.
	 * @param string $id the locale ID to be converted
	 * @return string the script ID
	 * @since 1.1.9
	 */
	public function getScriptID($id)
	{
		// normalize id
		$id = $this->getCanonicalID($id);
		// find sub tags
		if(($underscorePosition=strpos($id, '_'))!==false)
		{
			$subTag = explode('_', $id);
			// script sub tags can be distigused from territory sub tags by length
			if (strlen($subTag[1])===4)
			{
				$id = $subTag[1];
			}
			else
			{
				$id = null;
			}
		}
		else
		{
			$id = null;
		}
		return $id;
	}

	/**
	 * Converts a locale ID to a territory ID.
	 * A territory ID consists of only the last two to three letter or digits after an underscore or dash.
	 * @param string $id the locale ID to be converted
	 * @return string the territory ID
	 * @since 1.1.9
	 */
	public function getTerritoryID($id)
	{
		// normalize id
		$id = $this->getCanonicalID($id);
		// find sub tags
		if (($underscorePosition=strpos($id, '_'))!== false)
		{
			$subTag = explode('_', $id);
			// territory sub tags can be distigused from script sub tags by length
			if (isset($subTag[2]) && strlen($subTag[2])<4)
			{
				$id = $subTag[2];
			}
			elseif (strlen($subTag[1])<4)
			{
				$id = $subTag[1];
			}
			else
			{
				$id = null;
			}
		}
		else
		{
			$id = null;
		}
		return $id;
	}

	/**
	 * Gets a localized name from i18n data file (one of framework/i18n/data/ files).
	 *
	 * @param string $id array key from an array named by $category.
	 * @param string $category data category. One of 'languages', 'scripts' or 'territories'.
	 * @return string the localized name for the id specified. Null if data does not exist.
	 * @since 1.1.9
	 */
	public function getLocaleDisplayName($id=null, $category='languages')
	{
		$id = $this->getCanonicalID($id);
		if (($category == 'languages') && ($id=$this->getLanguageID($id)) && (isset($this->_data[$category][$id])))
		{
			return $this->_data[$category][$id];
		}
		elseif (($category == 'scripts') && ($id=$this->getScriptID($id)) && (isset($this->_data[$category][$id])))
		{
			return $this->_data[$category][$id];
		}
		elseif (($category == 'territories') && ($id=$this->getTerritoryID($id)) && (isset($this->_data[$category][$id])))
		{
			return $this->_data[$category][$id];
		}
		elseif (isset($this->_data[$category][$id]))
		{
			return $this->_data[$category][$id];
		}
		else {
			return null;
		}
	}

	/**
	 * @param string $id Unicode language identifier from IETF BCP 47. For example, the code "en_US" represents U.S. English and "en_GB" represents British English.
	 * @return string the local display name for the language. Null if the language code does not exist.
	 * @since 1.1.9
	 */
	public function getLanguage($id)
	{
		return $this->getLocaleDisplayName($id, 'languages');
	}

	/**
	 * @param string $id Unicode script identifier from IETF BCP 47. For example, the code "en_US" represents U.S. English and "en_GB" represents British English.
	 * @return string the local display name for the script. Null if the script code does not exist.
	 * @since 1.1.9
	 */
	public function getScript($id)
	{
		return $this->getLocaleDisplayName($id, 'scripts');
	}

	/**
	 * @param string $id Unicode territory identifier from IETF BCP 47. For example, the code "en_US" represents U.S. English and "en_GB" represents British English.
	 * @return string the local display name for the territory. Null if the territory code does not exist.
	 * @since 1.1.9
	 */
	public function getTerritory($id)
	{
		return $this->getLocaleDisplayName($id, 'territories');
	}
}