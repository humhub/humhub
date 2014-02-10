<?php
/**
 * YiiBase class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


/**
 * CChoiceFormat is a helper that chooses an appropriate message based on the specified number value.
 * The candidate messages are given as a string in the following format:
 * <pre>
 * 'expr1#message1|expr2#message2|expr3#message3'
 * </pre>
 * where each expression should be a valid PHP expression with 'n' as the only variable.
 * For example, 'n==1' and 'n%10==2 && n>10' are both valid expressions.
 * The variable 'n' will take the given number value, and if an expression evaluates true,
 * the corresponding message will be returned.
 *
 * For example, given the candidate messages 'n==1#one|n==2#two|n>2#others' and
 * the number value 2, the resulting message will be 'two'.
 *
 * For expressions like 'n==1', we can also use a shortcut '1'. So the above example
 * candidate messages can be simplified as '1#one|2#two|n>2#others'.
 *
 * In case the given number doesn't select any message, the last candidate message
 * will be returned.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.i18n
 */
class CChoiceFormat
{
	/**
	 * Formats a message according to the specified number value.
	 * @param string $messages the candidate messages in the format of 'expr1#message1|expr2#message2|expr3#message3'.
	 * See {@link CChoiceFormat} for more details.
	 * @param mixed $number the number value
	 * @return string the selected message
	 */
	public static function format($messages, $number)
	{
		$n=preg_match_all('/\s*([^#]*)\s*#([^\|]*)\|/',$messages.'|',$matches);
		if($n===0)
			return $messages;
		for($i=0;$i<$n;++$i)
		{
			$expression=$matches[1][$i];
			$message=$matches[2][$i];
			if($expression===(string)(int)$expression)
			{
				if($expression==$number)
					return $message;
			}
			elseif(self::evaluate(str_replace('n','$n',$expression),$number))
				return $message;
		}
		return $message; // return the last choice
	}

	/**
	 * Evaluates a PHP expression with the given number value.
	 * @param string $expression the PHP expression
	 * @param mixed $n the number value
	 * @return boolean the expression result
	 */
	protected static function evaluate($expression,$n)
	{
		return @eval("return $expression;");
	}
}