<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Ldap;

use DateTime;

/**
 * Zend\Ldap\Attribute is a collection of LDAP attribute related functions.
 */
class Attribute
{
    const PASSWORD_HASH_MD5   = 'md5';
    const PASSWORD_HASH_SMD5  = 'smd5';
    const PASSWORD_HASH_SHA   = 'sha';
    const PASSWORD_HASH_SSHA  = 'ssha';
    const PASSWORD_UNICODEPWD = 'unicodePwd';

    /**
     * Sets a LDAP attribute.
     *
     * @param  array                     $data
     * @param  string                    $attribName
     * @param  string|array|\Traversable $value
     * @param  bool                   $append
     * @return void
     */
    public static function setAttribute(array &$data, $attribName, $value, $append = false)
    {
        $attribName = strtolower($attribName);
        $valArray   = array();
        if (is_array($value) || ($value instanceof \Traversable)) {
            foreach ($value as $v) {
                $v = static::valueToLdap($v);
                if ($v !== null) {
                    $valArray[] = $v;
                }
            }
        } elseif ($value !== null) {
            $value = static::valueToLdap($value);
            if ($value !== null) {
                $valArray[] = $value;
            }
        }

        if ($append === true && isset($data[$attribName])) {
            if (is_string($data[$attribName])) {
                $data[$attribName] = array($data[$attribName]);
            }
            $data[$attribName] = array_merge($data[$attribName], $valArray);
        } else {
            $data[$attribName] = $valArray;
        }
    }

    /**
     * Gets a LDAP attribute.
     *
     * @param  array   $data
     * @param  string  $attribName
     * @param  int $index
     * @return array|mixed
     */
    public static function getAttribute(array $data, $attribName, $index = null)
    {
        $attribName = strtolower($attribName);
        if ($index === null) {
            if (!isset($data[$attribName])) {
                return array();
            }
            $retArray = array();
            foreach ($data[$attribName] as $v) {
                $retArray[] = static::valueFromLdap($v);
            }
            return $retArray;
        } elseif (is_int($index)) {
            if (!isset($data[$attribName])) {
                return null;
            } elseif ($index >= 0 && $index < count($data[$attribName])) {
                return static::valueFromLdap($data[$attribName][$index]);
            } else {
                return null;
            }
        }

        return null;
    }

    /**
     * Checks if the given value(s) exist in the attribute
     *
     * @param array       $data
     * @param string      $attribName
     * @param mixed|array $value
     * @return bool
     */
    public static function attributeHasValue(array &$data, $attribName, $value)
    {
        $attribName = strtolower($attribName);
        if (!isset($data[$attribName])) {
            return false;
        }

        if (is_scalar($value)) {
            $value = array($value);
        }

        foreach ($value as $v) {
            $v = self::valueToLdap($v);
            if (!in_array($v, $data[$attribName], true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Removes duplicate values from a LDAP attribute
     *
     * @param array  $data
     * @param string $attribName
     * @return void
     */
    public static function removeDuplicatesFromAttribute(array &$data, $attribName)
    {
        $attribName = strtolower($attribName);
        if (!isset($data[$attribName])) {
            return;
        }
        $data[$attribName] = array_values(array_unique($data[$attribName]));
    }

    /**
     * Remove given values from a LDAP attribute
     *
     * @param array       $data
     * @param string      $attribName
     * @param mixed|array $value
     * @return void
     */
    public static function removeFromAttribute(array &$data, $attribName, $value)
    {
        $attribName = strtolower($attribName);
        if (!isset($data[$attribName])) {
            return;
        }

        if (is_scalar($value)) {
            $value = array($value);
        }

        $valArray = array();
        foreach ($value as $v) {
            $v = self::valueToLdap($v);
            if ($v !== null) {
                $valArray[] = $v;
            }
        }

        $resultArray = $data[$attribName];
        foreach ($valArray as $rv) {
            $keys = array_keys($resultArray, $rv);
            foreach ($keys as $k) {
                unset($resultArray[$k]);
            }
        }
        $resultArray       = array_values($resultArray);
        $data[$attribName] = $resultArray;
    }

    /**
     * @param  mixed $value
     * @return string|null
     */
    private static function valueToLdap($value)
    {
        return Converter\Converter::toLdap($value);
    }

    /**
     * @param  string $value
     * @return mixed
     */
    private static function valueFromLdap($value)
    {
        try {
            $return = Converter\Converter::fromLdap($value, Converter\Converter::STANDARD, false);
            if ($return instanceof DateTime) {
                return Converter\Converter::toLdapDateTime($return, false);
            } else {
                return $return;
            }
        } catch (Exception\InvalidArgumentException $e) {
            return $value;
        }
    }

    /**
     * Sets a LDAP password.
     *
     * @param array  $data
     * @param string $password
     * @param string $hashType   Optional by default MD5
     * @param string $attribName Optional
     */
    public static function setPassword(
        array &$data, $password, $hashType = self::PASSWORD_HASH_MD5,
        $attribName = null
    )
    {
        if ($attribName === null) {
            if ($hashType === self::PASSWORD_UNICODEPWD) {
                $attribName = 'unicodePwd';
            } else {
                $attribName = 'userPassword';
            }
        }

        $hash = static::createPassword($password, $hashType);
        static::setAttribute($data, $attribName, $hash, false);
    }

    /**
     * Creates a LDAP password.
     *
     * @param  string $password
     * @param  string $hashType
     * @return string
     */
    public static function createPassword($password, $hashType = self::PASSWORD_HASH_MD5)
    {
        switch ($hashType) {
            case self::PASSWORD_UNICODEPWD:
                /* see:
                 * http://msdn.microsoft.com/en-us/library/cc223248(PROT.10).aspx
                 */
                $password = '"' . $password . '"';
                if (function_exists('mb_convert_encoding')) {
                    $password = mb_convert_encoding($password, 'UTF-16LE', 'UTF-8');
                } elseif (function_exists('iconv')) {
                    $password = iconv('UTF-8', 'UTF-16LE', $password);
                } else {
                    $len = strlen($password);
                    $new = '';
                    for ($i = 0; $i < $len; $i++) {
                        $new .= $password[$i] . "\x00";
                    }
                    $password = $new;
                }
                return $password;
            case self::PASSWORD_HASH_SSHA:
                $salt    = substr(sha1(uniqid(mt_rand(), true), true), 0, 4);
                $rawHash = sha1($password . $salt, true) . $salt;
                $method  = '{SSHA}';
                break;
            case self::PASSWORD_HASH_SHA:
                $rawHash = sha1($password, true);
                $method  = '{SHA}';
                break;
            case self::PASSWORD_HASH_SMD5:
                $salt    = substr(sha1(uniqid(mt_rand(), true), true), 0, 4);
                $rawHash = md5($password . $salt, true) . $salt;
                $method  = '{SMD5}';
                break;
            case self::PASSWORD_HASH_MD5:
            default:
                $rawHash = md5($password, true);
                $method  = '{MD5}';
                break;
        }
        return $method . base64_encode($rawHash);
    }

    /**
     * Sets a LDAP date/time attribute.
     *
     * @param  array                      $data
     * @param  string                     $attribName
     * @param  int|array|\Traversable $value
     * @param  bool                    $utc
     * @param  bool                    $append
     */
    public static function setDateTimeAttribute(
        array &$data, $attribName, $value, $utc = false,
        $append = false
    )
    {
        $convertedValues = array();
        if (is_array($value) || ($value instanceof \Traversable)) {
            foreach ($value as $v) {
                $v = static::valueToLdapDateTime($v, $utc);
                if ($v !== null) {
                    $convertedValues[] = $v;
                }
            }
        } elseif ($value !== null) {
            $value = static::valueToLdapDateTime($value, $utc);
            if ($value !== null) {
                $convertedValues[] = $value;
            }
        }
        static::setAttribute($data, $attribName, $convertedValues, $append);
    }

    /**
     * @param  int $value
     * @param  bool $utc
     * @return string|null
     */
    private static function valueToLdapDateTime($value, $utc)
    {
        if (is_int($value)) {
            return Converter\Converter::toLdapDateTime($value, $utc);
        }

        return null;
    }

    /**
     * Gets a LDAP date/time attribute.
     *
     * @param  array   $data
     * @param  string  $attribName
     * @param  int $index
     * @return array|int
     */
    public static function getDateTimeAttribute(array $data, $attribName, $index = null)
    {
        $values = static::getAttribute($data, $attribName, $index);
        if (is_array($values)) {
            for ($i = 0; $i < count($values); $i++) {
                $newVal = static::valueFromLdapDateTime($values[$i]);
                if ($newVal !== null) {
                    $values[$i] = $newVal;
                }
            }
        } else {
            $newVal = static::valueFromLdapDateTime($values);
            if ($newVal !== null) {
                $values = $newVal;
            }
        }

        return $values;
    }

    /**
     * @param  string|DateTime $value
     * @return int|null
     */
    private static function valueFromLdapDateTime($value)
    {
        if ($value instanceof DateTime) {
            return $value->format('U');
        } elseif (is_string($value)) {
            try {
                return Converter\Converter::fromLdapDateTime($value, false)->format('U');
            } catch (Converter\Exception\InvalidArgumentException $e) {
                return null;
            }
        }

        return null;
    }
}
