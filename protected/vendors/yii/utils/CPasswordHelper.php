<?php
/**
 * CPasswordHelper class file.
 *
 * @author Tom Worster <fsb@thefsb.org>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CPasswordHelper provides a simple API for secure password hashing and verification.
 *
 * CPasswordHelper uses the Blowfish hash algorithm available in many PHP runtime
 * environments through the PHP {@link http://php.net/manual/en/function.crypt.php crypt()}
 * built-in function. As of Dec 2012 it is the strongest algorithm available in PHP
 * and the only algorithm without some security concerns surrounding it. For this reason,
 * CPasswordHelper fails to initialize when run in and environment that does not have
 * crypt() and its Blowfish option. Systems with the option include:
 * (1) Most *nix systems since PHP 4 (the algorithm is part of the library function crypt(3));
 * (2) All PHP systems since 5.3.0; (3) All PHP systems with the
 * {@link http://www.hardened-php.net/suhosin/ Suhosin patch}.
 * For more information about password hashing, crypt() and Blowfish, please read
 * the Yii Wiki article
 * {@link http://www.yiiframework.com/wiki/425/use-crypt-for-password-storage/ Use crypt() for password storage}.
 * and the
 * PHP RFC {@link http://wiki.php.net/rfc/password_hash Adding simple password hashing API}.
 *
 * CPasswordHelper throws an exception if the Blowfish hash algorithm is not
 * available in the runtime PHP's crypt() function. It can be used as follows
 *
 * Generate a hash from a password:
 * <pre>
 * $hash = CPasswordHelper::hashPassword($password);
 * </pre>
 * This hash can be stored in a database (e.g. CHAR(64) CHARACTER SET latin1). The
 * hash is usually generated and saved to the database when the user enters a new password.
 * But it can also be useful to generate and save a hash after validating a user's
 * password in order to change the cost or refresh the salt.
 *
 * To verify a password, fetch the user's saved hash from the database (into $hash) and:
 * <pre>
 * if (CPasswordHelper::verifyPassword($password, $hash))
 *     // password is good
 * else
 *     // password is bad
 * </pre>
 *
 * @author Tom Worster <fsb@thefsb.org>
 * @package system.utils
 * @since 1.1.14
 */
class CPasswordHelper
{
	/**
	 * Check for availability of PHP crypt() with the Blowfish hash option.
	 * @throws CException if the runtime system does not have PHP crypt() or its Blowfish hash option.
	 */
	protected static function checkBlowfish()
	{
		if(!function_exists('crypt'))
			throw new CException(Yii::t('yii','{class} requires the PHP crypt() function. This system does not have it.',
				array('{class}'=>__CLASS__)));

		if(!defined('CRYPT_BLOWFISH') || !CRYPT_BLOWFISH)
			throw new CException(Yii::t('yii',
				'{class} requires the Blowfish option of the PHP crypt() function. This system does not have it.',
				array('{class}'=>__CLASS__)));
    }

	/**
	 * Generate a secure hash from a password and a random salt.
	 *
	 * Uses the
	 * PHP {@link http://php.net/manual/en/function.crypt.php crypt()} built-in function
	 * with the Blowfish hash option.
	 *
	 * @param string $password The password to be hashed.
	 * @param int $cost Cost parameter used by the Blowfish hash algorithm.
	 * The higher the value of cost,
	 * the longer it takes to generate the hash and to verify a password against it. Higher cost
	 * therefore slows down a brute-force attack. For best protection against brute for attacks,
	 * set it to the highest value that is tolerable on production servers. The time taken to
	 * compute the hash doubles for every increment by one of $cost. So, for example, if the
	 * hash takes 1 second to compute when $cost is 14 then then the compute time varies as
	 * 2^($cost - 14) seconds.
	 * @return string The password hash string, ASCII and not longer than 64 characters.
	 * @throws CException on bad password parameter or if crypt() with Blowfish hash is not available.
	 */
	public static function hashPassword($password,$cost=13)
	{
		self::checkBlowfish();
		$salt=self::generateSalt($cost);
		$hash=crypt($password,$salt);

		if(!is_string($hash) || (function_exists('mb_strlen') ? mb_strlen($hash, '8bit') : strlen($hash))<32)
			throw new CException(Yii::t('yii','Internal error while generating hash.'));

		return $hash;
    }

	/**
	 * Verify a password against a hash.
	 *
	 * @param string $password The password to verify. If password is empty or not a string, method will return false.
	 * @param string $hash The hash to verify the password against.
	 * @return bool True if the password matches the hash.
	 * @throws CException on bad password or hash parameters or if crypt() with Blowfish hash is not available.
	 */
	public static function verifyPassword($password, $hash)
	{
		self::checkBlowfish();
		if(!is_string($password) || $password==='')
			return false;

		if (!$password || !preg_match('{^\$2[axy]\$(\d\d)\$[\./0-9A-Za-z]{22}}',$hash,$matches) ||
			$matches[1]<4 || $matches[1]>31)
			return false;

		$test=crypt($password,$hash);
		if(!is_string($test) || strlen($test)<32)
			return false;

		return self::same($test, $hash);
	}

	/**
	 * Check for sameness of two strings using an algorithm with timing
	 * independent of the string values if the subject strings are of equal length.
	 *
	 * The function can be useful to prevent timing attacks. For example, if $a and $b
	 * are both hash values from the same algorithm, then the timing of this function
	 * does not reveal whether or not there is a match.
	 *
	 * NOTE: timing is affected if $a and $b are different lengths or either is not a
	 * string. For the purpose of checking password hash this does not reveal information
	 * useful to an attacker.
	 *
	 * @see http://blog.astrumfutura.com/2010/10/nanosecond-scale-remote-timing-attacks-on-php-applications-time-to-take-them-seriously/
	 * @see http://codereview.stackexchange.com/questions/13512
	 * @see https://github.com/ircmaxell/password_compat/blob/master/lib/password.php
	 *
	 * @param string $a First subject string to compare.
	 * @param string $b Second subject string to compare.
	 * @return bool true if the strings are the same, false if they are different or if
	 * either is not a string.
	 */
	public static function same($a,$b)
	{
		if(!is_string($a) || !is_string($b))
			return false;

		$mb=function_exists('mb_strlen');
		$length=$mb ? mb_strlen($a,'8bit') : strlen($a);
		if($length!==($mb ? mb_strlen($b,'8bit') : strlen($b)))
			return false;

		$check=0;
		for($i=0;$i<$length;$i+=1)
			$check|=(ord($a[$i])^ord($b[$i]));

		return $check===0;
	}

	/**
	 * Generates a salt that can be used to generate a password hash.
	 *
	 * The PHP {@link http://php.net/manual/en/function.crypt.php crypt()} built-in function
	 * requires, for the Blowfish hash algorithm, a salt string in a specific format:
	 *  "$2a$" (in which the "a" may be replaced by "x" or "y" see PHP manual for details),
	 *  a two digit cost parameter,
	 *  "$",
	 *  22 characters from the alphabet "./0-9A-Za-z".
	 *
	 * @param int $cost Cost parameter used by the Blowfish hash algorithm.
	 * @return string the random salt value.
	 * @throws CException in case of invalid cost number
	 */
	public static function generateSalt($cost=13)
	{
		if(!is_numeric($cost))
			throw new CException(Yii::t('yii','{class}::$cost must be a number.',array('{class}'=>__CLASS__)));

		$cost=(int)$cost;
		if($cost<4 || $cost>31)
		    throw new CException(Yii::t('yii','{class}::$cost must be between 4 and 31.',array('{class}'=>__CLASS__)));

		if(($random=Yii::app()->getSecurityManager()->generateRandomString(22,true))===false)
			if(($random=Yii::app()->getSecurityManager()->generateRandomString(22,false))===false)
				throw new CException(Yii::t('yii','Unable to generate random string.'));
		return sprintf('$2a$%02d$',$cost).strtr($random,array('_'=>'.','~'=>'/'));
	}
}
