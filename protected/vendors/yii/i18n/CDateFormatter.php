<?php
/**
 * CDateFormatter class file.
 *
 * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDateFormatter provides date/time localization functionalities.
 *
 * CDateFormatter allows you to format dates and times in a locale-sensitive manner.
 * Patterns are interpreted in the locale that the CDateFormatter instance
 * is associated with. For example, month names and weekday names may vary
 * under different locales, which yields different formatting results.
 * The patterns that CDateFormatter recognizes are as defined in
 * {@link http://www.unicode.org/reports/tr35/#Date_Format_Patterns CLDR}.
 *
 * CDateFormatter supports predefined patterns as well as customized ones:
 * <ul>
 * <li>The method {@link formatDateTime()} formats date or time or both using
 *   predefined patterns which include 'full', 'long', 'medium' (default) and 'short'.</li>
 * <li>The method {@link format()} formats datetime using the specified pattern.
 *   See {@link http://www.unicode.org/reports/tr35/#Date_Format_Patterns} for
 *   details about the recognized pattern characters.</li>
 * </ul>
 *
 * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.i18n
 * @since 1.0
 */
class CDateFormatter extends CComponent
{
	/**
	 * @var array pattern characters mapping to the corresponding translator methods
	 */
	private static $_formatters=array(
		'G'=>'formatEra',
		'y'=>'formatYear',
		'M'=>'formatMonth',
		'L'=>'formatMonth',
		'd'=>'formatDay',
		'h'=>'formatHour12',
		'H'=>'formatHour24',
		'm'=>'formatMinutes',
		's'=>'formatSeconds',
		'E'=>'formatDayInWeek',
		'c'=>'formatDayInWeek',
		'e'=>'formatDayInWeek',
		'D'=>'formatDayInYear',
		'F'=>'formatDayInMonth',
		'w'=>'formatWeekInYear',
		'W'=>'formatWeekInMonth',
		'a'=>'formatPeriod',
		'k'=>'formatHourInDay',
		'K'=>'formatHourInPeriod',
		'z'=>'formatTimeZone',
		'Z'=>'formatTimeZone',
		'v'=>'formatTimeZone',
	);

	private $_locale;

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
	 * Formats a date according to a customized pattern.
	 * @param string $pattern the pattern (See {@link http://www.unicode.org/reports/tr35/#Date_Format_Patterns})
	 * @param mixed $time UNIX timestamp or a string in strtotime format
	 * @return string formatted date time. Null if $time is null. (the null value check is available since Yii 1.1.11)
	 */
	public function format($pattern,$time)
	{
		if($time===null)
			return null;

		if(is_string($time))
		{
			if(ctype_digit($time) || ($time{0}=='-' && ctype_digit(substr($time, 1))))
				$time=(int)$time;
			else
				$time=strtotime($time);
		}
		$date=CTimestamp::getDate($time,false,false);
		$tokens=$this->parseFormat($pattern);
		foreach($tokens as &$token)
		{
			if(is_array($token)) // a callback: method name, sub-pattern
				$token=$this->{$token[0]}($token[1],$date);
		}
		return implode('',$tokens);
	}

	/**
	 * Formats a date according to a predefined pattern.
	 * The predefined pattern is determined based on the date pattern width and time pattern width.
	 * @param mixed $timestamp UNIX timestamp or a string in strtotime format
	 * @param string $dateWidth width of the date pattern. It can be 'full', 'long', 'medium' and 'short'.
	 * If null, it means the date portion will NOT appear in the formatting result
	 * @param string $timeWidth width of the time pattern. It can be 'full', 'long', 'medium' and 'short'.
	 * If null, it means the time portion will NOT appear in the formatting result
	 * @return string formatted date time.
	 */
	public function formatDateTime($timestamp,$dateWidth='medium',$timeWidth='medium')
	{
		if(!empty($dateWidth))
			$date=$this->format($this->_locale->getDateFormat($dateWidth),$timestamp);

		if(!empty($timeWidth))
			$time=$this->format($this->_locale->getTimeFormat($timeWidth),$timestamp);

		if(isset($date) && isset($time))
		{
			$dateTimePattern=$this->_locale->getDateTimeFormat();
			return strtr($dateTimePattern,array('{0}'=>$time,'{1}'=>$date));
		}
		elseif(isset($date))
			return $date;
		elseif(isset($time))
			return $time;
	}

	/**
	 * Parses the datetime format pattern.
	 * @param string $pattern the pattern to be parsed
	 * @return array tokenized parsing result
	 */
	protected function parseFormat($pattern)
	{
		static $formats=array();  // cache
		if(isset($formats[$pattern]))
			return $formats[$pattern];
		$tokens=array();
		$n=strlen($pattern);
		$isLiteral=false;
		$literal='';
		for($i=0;$i<$n;++$i)
		{
			$c=$pattern[$i];
			if($c==="'")
			{
				if($i<$n-1 && $pattern[$i+1]==="'")
				{
					$tokens[]="'";
					$i++;
				}
				elseif($isLiteral)
				{
					$tokens[]=$literal;
					$literal='';
					$isLiteral=false;
				}
				else
				{
					$isLiteral=true;
					$literal='';
				}
			}
			elseif($isLiteral)
				$literal.=$c;
			else
			{
				for($j=$i+1;$j<$n;++$j)
				{
					if($pattern[$j]!==$c)
						break;
				}
				$p=str_repeat($c,$j-$i);
				if(isset(self::$_formatters[$c]))
					$tokens[]=array(self::$_formatters[$c],$p);
				else
					$tokens[]=$p;
				$i=$j-1;
			}
		}
		if($literal!=='')
			$tokens[]=$literal;

		return $formats[$pattern]=$tokens;
	}

	/**
	 * Get the year.
 	 * "yy" will return the last two digits of year.
 	 * "y...y" will pad the year with 0 in the front, e.g. "yyyyy" will generate "02008" for year 2008.
	 * @param string $pattern a pattern.
	 * @param array $date result of {@link CTimestamp::getdate}.
	 * @return string formatted year
	 */
	protected function formatYear($pattern,$date)
	{
		$year=$date['year'];
		if($pattern==='yy')
			return str_pad($year%100,2,'0',STR_PAD_LEFT);
		else
			return str_pad($year,strlen($pattern),'0',STR_PAD_LEFT);
	}

	/**
	 * Get the month.
 	 * "M" will return integer 1 through 12;
 	 * "MM" will return two digits month number with necessary zero padding, e.g. 05;
 	 * "MMM" will return the abrreviated month name, e.g. "Jan";
 	 * "MMMM" will return the full month name, e.g. "January";
 	 * "MMMMM" will return the narrow month name, e.g. "J";
	 * @param string $pattern a pattern.
	 * @param array $date result of {@link CTimestamp::getdate}.
	 * @return string month name
	 */
	protected function formatMonth($pattern,$date)
	{
		$month=$date['mon'];
		switch($pattern)
		{
			case 'M':
				return $month;
			case 'MM':
				return str_pad($month,2,'0',STR_PAD_LEFT);
			case 'MMM':
				return $this->_locale->getMonthName($month,'abbreviated');
			case 'MMMM':
				return $this->_locale->getMonthName($month,'wide');
			case 'MMMMM':
				return $this->_locale->getMonthName($month,'narrow');
			case 'L':
				return $month;
			case 'LL':
				return str_pad($month,2,'0',STR_PAD_LEFT);
			case 'LLL':
				return $this->_locale->getMonthName($month,'abbreviated', true);
			case 'LLLL':
				return $this->_locale->getMonthName($month,'wide', true);
			case 'LLLLL':
				return $this->_locale->getMonthName($month,'narrow', true);
			default:
				throw new CException(Yii::t('yii','The pattern for month must be "M", "MM", "MMM", "MMMM", "L", "LL", "LLL" or "LLLL".'));
		}
	}

	/**
	 * Get the day of the month.
 	 * "d" for non-padding, "dd" will always return 2 digits day numbers, e.g. 05.
	 * @param string $pattern a pattern.
	 * @param array $date result of {@link CTimestamp::getdate}.
	 * @return string day of the month
	 */
	protected function formatDay($pattern,$date)
	{
		$day=$date['mday'];
		if($pattern==='d')
			return $day;
		elseif($pattern==='dd')
			return str_pad($day,2,'0',STR_PAD_LEFT);
		else
			throw new CException(Yii::t('yii','The pattern for day of the month must be "d" or "dd".'));
	}

	/**
	 * Get the day in the year, e.g. [1-366]
	 * @param string $pattern a pattern.
	 * @param array $date result of {@link CTimestamp::getdate}.
	 * @return integer hours in AM/PM format.
	 */
	protected function formatDayInYear($pattern,$date)
	{
		$day=$date['yday'];
		if(($n=strlen($pattern))<=3)
			return str_pad($day,$n,'0',STR_PAD_LEFT);
		else
			throw new CException(Yii::t('yii','The pattern for day in year must be "D", "DD" or "DDD".'));
	}

	/**
	 * Get day of week in the month, e.g. 2nd Wed in July.
	 * @param string $pattern a pattern.
	 * @param array $date result of {@link CTimestamp::getdate}.
	 * @return integer day in month
	 * @see http://www.unicode.org/reports/tr35/#Date_Format_Patterns
	 */
	protected function formatDayInMonth($pattern,$date)
	{
		if($pattern==='F')
			return (int)(($date['mday']+6)/7);
		else
			throw new CException(Yii::t('yii','The pattern for day in month must be "F".'));
	}

	/**
	 * Get the day of the week.
 	 * "E", "EE", "EEE" will return abbreviated week day name, e.g. "Tues";
 	 * "EEEE" will return full week day name;
 	 * "EEEEE" will return the narrow week day name, e.g. "T";
	 * @param string $pattern a pattern.
	 * @param array $date result of {@link CTimestamp::getdate}.
	 * @return string day of the week.
	 * @see http://www.unicode.org/reports/tr35/#Date_Format_Patterns
	 */
	protected function formatDayInWeek($pattern,$date)
	{
		$day=$date['wday'];
		switch($pattern)
		{
			case 'E':
			case 'EE':
			case 'EEE':
			case 'eee':
				return $this->_locale->getWeekDayName($day,'abbreviated');
			case 'EEEE':
			case 'eeee':
				return $this->_locale->getWeekDayName($day,'wide');
			case 'EEEEE':
			case 'eeeee':
				return $this->_locale->getWeekDayName($day,'narrow');
			case 'e':
			case 'ee':
			case 'c':
				return $day ? $day : 7;
			case 'ccc':
				return $this->_locale->getWeekDayName($day,'abbreviated',true);
			case 'cccc':
				return $this->_locale->getWeekDayName($day,'wide',true);
			case 'ccccc':
				return $this->_locale->getWeekDayName($day,'narrow',true);
			default:
				throw new CException(Yii::t('yii','The pattern for day of the week must be "E", "EE", "EEE", "EEEE", "EEEEE", "e", "ee", "eee", "eeee", "eeeee", "c", "cccc" or "ccccc".'));
		}
	}

	/**
	 * Get the AM/PM designator, 12 noon is PM, 12 midnight is AM.
	 * @param string $pattern a pattern.
	 * @param array $date result of {@link CTimestamp::getdate}.
	 * @return string AM or PM designator
	 */
	protected function formatPeriod($pattern,$date)
	{
		if($pattern==='a')
		{
			if(intval($date['hours']/12))
				return $this->_locale->getPMName();
			else
				return $this->_locale->getAMName();
		}
		else
			throw new CException(Yii::t('yii','The pattern for AM/PM marker must be "a".'));
	}

	/**
	 * Get the hours in 24 hour format, i.e. [0-23].
	 * "H" for non-padding, "HH" will always return 2 characters.
	 * @param string $pattern a pattern.
	 * @param array $date result of {@link CTimestamp::getdate}.
	 * @return string hours in 24 hour format.
	 */
	protected function formatHour24($pattern,$date)
	{
		$hour=$date['hours'];
		if($pattern==='H')
			return $hour;
		elseif($pattern==='HH')
			return str_pad($hour,2,'0',STR_PAD_LEFT);
		else
			throw new CException(Yii::t('yii','The pattern for 24 hour format must be "H" or "HH".'));
	}

	/**
	 * Get the hours in 12 hour format, i.e., [1-12]
	 * "h" for non-padding, "hh" will always return 2 characters.
	 * @param string $pattern a pattern.
	 * @param array $date result of {@link CTimestamp::getdate}.
	 * @return string hours in 12 hour format.
	 */
	protected function formatHour12($pattern,$date)
	{
		$hour=$date['hours'];
		$hour=($hour==12|$hour==0)?12:($hour)%12;
		if($pattern==='h')
			return $hour;
		elseif($pattern==='hh')
			return str_pad($hour,2,'0',STR_PAD_LEFT);
		else
			throw new CException(Yii::t('yii','The pattern for 12 hour format must be "h" or "hh".'));
	}

	/**
	 * Get the hours [1-24].
	 * 'k' for non-padding, and 'kk' with 2 characters padding.
	 * @param string $pattern a pattern.
	 * @param array $date result of {@link CTimestamp::getdate}.
	 * @return integer hours [1-24]
	 */
	protected function formatHourInDay($pattern,$date)
	{
		$hour=$date['hours']==0?24:$date['hours'];
		if($pattern==='k')
			return $hour;
		elseif($pattern==='kk')
			return str_pad($hour,2,'0',STR_PAD_LEFT);
		else
			throw new CException(Yii::t('yii','The pattern for hour in day must be "k" or "kk".'));
	}

	/**
	 * Get the hours in AM/PM format, e.g [0-11]
	 * "K" for non-padding, "KK" will always return 2 characters.
	 * @param string $pattern a pattern.
	 * @param array $date result of {@link CTimestamp::getdate}.
	 * @return integer hours in AM/PM format.
	 */
	protected function formatHourInPeriod($pattern,$date)
	{
		$hour=$date['hours']%12;
		if($pattern==='K')
			return $hour;
		elseif($pattern==='KK')
			return str_pad($hour,2,'0',STR_PAD_LEFT);
		else
			throw new CException(Yii::t('yii','The pattern for hour in AM/PM must be "K" or "KK".'));
	}

	/**
	 * Get the minutes.
	 * "m" for non-padding, "mm" will always return 2 characters.
	 * @param string $pattern a pattern.
	 * @param array $date result of {@link CTimestamp::getdate}.
	 * @return string minutes.
	 */
	protected function formatMinutes($pattern,$date)
	{
		$minutes=$date['minutes'];
		if($pattern==='m')
			return $minutes;
		elseif($pattern==='mm')
			return str_pad($minutes,2,'0',STR_PAD_LEFT);
		else
			throw new CException(Yii::t('yii','The pattern for minutes must be "m" or "mm".'));
	}

	/**
	 * Get the seconds.
	 * "s" for non-padding, "ss" will always return 2 characters.
	 * @param string $pattern a pattern.
	 * @param array $date result of {@link CTimestamp::getdate}.
	 * @return string seconds
	 */
	protected function formatSeconds($pattern,$date)
	{
		$seconds=$date['seconds'];
		if($pattern==='s')
			return $seconds;
		elseif($pattern==='ss')
			return str_pad($seconds,2,'0',STR_PAD_LEFT);
		else
			throw new CException(Yii::t('yii','The pattern for seconds must be "s" or "ss".'));
	}

	/**
	 * Get the week in the year.
	 * @param string $pattern a pattern.
	 * @param array $date result of {@link CTimestamp::getdate}.
	 * @return integer week in year
	 */
	protected function formatWeekInYear($pattern,$date)
	{
		if($pattern==='w')
			return @date('W',@mktime(0,0,0,$date['mon'],$date['mday'],$date['year']));
		else
			throw new CException(Yii::t('yii','The pattern for week in year must be "w".'));
	}

	/**
	 * Get week in the month.
	 * @param array $pattern result of {@link CTimestamp::getdate}.
	 * @param string $date a pattern.
	 * @return integer week in month
	 */
	protected function formatWeekInMonth($pattern,$date)
	{
		if($pattern==='W')
			return @date('W',@mktime(0,0,0,$date['mon'], $date['mday'],$date['year']))-date('W', mktime(0,0,0,$date['mon'],1,$date['year']))+1;
		else
			throw new CException(Yii::t('yii','The pattern for week in month must be "W".'));
	}

	/**
	 * Get the timezone of the server machine.
	 * @param string $pattern a pattern.
	 * @param array $date result of {@link CTimestamp::getdate}.
	 * @return string time zone
	 * @todo How to get the timezone for a different region?
	 */
	protected function formatTimeZone($pattern,$date)
	{
		if($pattern[0]==='z' || $pattern[0]==='v')
			return @date('T', @mktime($date['hours'], $date['minutes'], $date['seconds'], $date['mon'], $date['mday'], $date['year']));
		elseif($pattern[0]==='Z')
			return @date('O', @mktime($date['hours'], $date['minutes'], $date['seconds'], $date['mon'], $date['mday'], $date['year']));
		else
			throw new CException(Yii::t('yii','The pattern for time zone must be "z" or "v".'));
	}

	/**
	 * Get the era. i.e. in gregorian, year > 0 is AD, else BC.
	 * @param string $pattern a pattern.
	 * @param array $date result of {@link CTimestamp::getdate}.
	 * @return string era
	 * @todo How to support multiple Eras?, e.g. Japanese.
	 */
	protected function formatEra($pattern,$date)
	{
		$era=$date['year']>0 ? 1 : 0;
		switch($pattern)
		{
			case 'G':
			case 'GG':
			case 'GGG':
				return $this->_locale->getEraName($era,'abbreviated');
			case 'GGGG':
				return $this->_locale->getEraName($era,'wide');
			case 'GGGGG':
				return $this->_locale->getEraName($era,'narrow');
			default:
				throw new CException(Yii::t('yii','The pattern for era must be "G", "GG", "GGG", "GGGG" or "GGGGG".'));
		}
	}
}
