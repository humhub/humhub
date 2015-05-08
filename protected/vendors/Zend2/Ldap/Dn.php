<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Ldap;

use ArrayAccess;

/**
 * Zend\Ldap\Dn provides an API for DN manipulation
 */
class Dn implements ArrayAccess
{
    const ATTR_CASEFOLD_NONE  = 'none';
    const ATTR_CASEFOLD_UPPER = 'upper';
    const ATTR_CASEFOLD_LOWER = 'lower';

    /**
     * The default case fold to use
     *
     * @var string
     */
    protected static $defaultCaseFold = self::ATTR_CASEFOLD_NONE;

    /**
     * The case fold used for this instance
     *
     * @var string
     */
    protected $caseFold;

    /**
     * The DN data
     *
     * @var array
     */
    protected $dn;

    /**
     * Creates a DN from an array or a string
     *
     * @param  string|array $dn
     * @param  string|null  $caseFold
     * @return Dn
     * @throws Exception\LdapException
     */
    public static function factory($dn, $caseFold = null)
    {
        if (is_array($dn)) {
            return static::fromArray($dn, $caseFold);
        } elseif (is_string($dn)) {
            return static::fromString($dn, $caseFold);
        }
        throw new Exception\LdapException(null, 'Invalid argument type for $dn');
    }

    /**
     * Creates a DN from a string
     *
     * @param  string      $dn
     * @param  string|null $caseFold
     * @return Dn
     * @throws Exception\LdapException
     */
    public static function fromString($dn, $caseFold = null)
    {
        $dn = trim($dn);
        if (empty($dn)) {
            $dnArray = array();
        } else {
            $dnArray = static::explodeDn((string) $dn);
        }
        return new static($dnArray, $caseFold);
    }

    /**
     * Creates a DN from an array
     *
     * @param  array       $dn
     * @param  string|null $caseFold
     * @return Dn
     * @throws Exception\LdapException
     */
    public static function fromArray(array $dn, $caseFold = null)
    {
        return new static($dn, $caseFold);
    }

    /**
     * Constructor
     *
     * @param array       $dn
     * @param string|null $caseFold
     */
    protected function __construct(array $dn, $caseFold)
    {
        $this->dn = $dn;
        $this->setCaseFold($caseFold);
    }

    /**
     * Gets the RDN of the current DN
     *
     * @param  string $caseFold
     * @return array
     * @throws Exception\LdapException if DN has no RDN (empty array)
     */
    public function getRdn($caseFold = null)
    {
        $caseFold = static::sanitizeCaseFold($caseFold, $this->caseFold);
        return static::caseFoldRdn($this->get(0, 1, $caseFold), null);
    }

    /**
     * Gets the RDN of the current DN as a string
     *
     * @param  string $caseFold
     * @return string
     * @throws Exception\LdapException if DN has no RDN (empty array)
     */
    public function getRdnString($caseFold = null)
    {
        $caseFold = static::sanitizeCaseFold($caseFold, $this->caseFold);
        return static::implodeRdn($this->getRdn(), $caseFold);
    }

    /**
     * Get the parent DN $levelUp levels up the tree
     *
     * @param  int $levelUp
     * @throws Exception\LdapException
     * @return Dn
     */
    public function getParentDn($levelUp = 1)
    {
        $levelUp = (int) $levelUp;
        if ($levelUp < 1 || $levelUp >= count($this->dn)) {
            throw new Exception\LdapException(null, 'Cannot retrieve parent DN with given $levelUp');
        }
        $newDn = array_slice($this->dn, $levelUp);
        return new static($newDn, $this->caseFold);
    }

    /**
     * Get a DN part
     *
     * @param  int    $index
     * @param  int    $length
     * @param  string $caseFold
     * @return array
     * @throws Exception\LdapException if index is illegal
     */
    public function get($index, $length = 1, $caseFold = null)
    {
        $caseFold = static::sanitizeCaseFold($caseFold, $this->caseFold);
        $this->assertIndex($index);
        $length = (int) $length;
        if ($length <= 0) {
            $length = 1;
        }
        if ($length === 1) {
            return static::caseFoldRdn($this->dn[$index], $caseFold);
        }
        return static::caseFoldDn(array_slice($this->dn, $index, $length, false), $caseFold);
    }

    /**
     * Set a DN part
     *
     * @param  int   $index
     * @param  array $value
     * @return Dn Provides a fluent interface
     * @throws Exception\LdapException if index is illegal
     */
    public function set($index, array $value)
    {
        $this->assertIndex($index);
        static::assertRdn($value);
        $this->dn[$index] = $value;
        return $this;
    }

    /**
     * Remove a DN part
     *
     * @param  int $index
     * @param  int $length
     * @return Dn Provides a fluent interface
     * @throws Exception\LdapException if index is illegal
     */
    public function remove($index, $length = 1)
    {
        $this->assertIndex($index);
        $length = (int) $length;
        if ($length <= 0) {
            $length = 1;
        }
        array_splice($this->dn, $index, $length, null);
        return $this;
    }

    /**
     * Append a DN part
     *
     * @param  array $value
     * @return Dn Provides a fluent interface
     */
    public function append(array $value)
    {
        static::assertRdn($value);
        $this->dn[] = $value;
        return $this;
    }

    /**
     * Prepend a DN part
     *
     * @param  array $value
     * @return Dn Provides a fluent interface
     */
    public function prepend(array $value)
    {
        static::assertRdn($value);
        array_unshift($this->dn, $value);
        return $this;
    }

    /**
     * Insert a DN part
     *
     * @param  int   $index
     * @param  array $value
     * @return Dn Provides a fluent interface
     * @throws Exception\LdapException if index is illegal
     */
    public function insert($index, array $value)
    {
        $this->assertIndex($index);
        static::assertRdn($value);
        $first    = array_slice($this->dn, 0, $index + 1);
        $second   = array_slice($this->dn, $index + 1);
        $this->dn = array_merge($first, array($value), $second);
        return $this;
    }

    /**
     * Assert index is correct and usable
     *
     * @param  mixed $index
     * @return bool
     * @throws Exception\LdapException
     */
    protected function assertIndex($index)
    {
        if (!is_int($index)) {
            throw new Exception\LdapException(null, 'Parameter $index must be an integer');
        }
        if ($index < 0 || $index >= count($this->dn)) {
            throw new Exception\LdapException(null, 'Parameter $index out of bounds');
        }
        return true;
    }

    /**
     * Assert if value is in a correct RDN format
     *
     * @param  array $value
     * @return bool
     * @throws Exception\LdapException
     */
    protected static function assertRdn(array $value)
    {
        if (count($value) < 1) {
            throw new Exception\LdapException(null, 'RDN Array is malformed: it must have at least one item');
        }

        foreach (array_keys($value) as $key) {
            if (!is_string($key)) {
                throw new Exception\LdapException(null, 'RDN Array is malformed: it must use string keys');
            }
        }
    }

    /**
     * Sets the case fold
     *
     * @param string|null $caseFold
     */
    public function setCaseFold($caseFold)
    {
        $this->caseFold = static::sanitizeCaseFold($caseFold, static::$defaultCaseFold);
    }

    /**
     * Return DN as a string
     *
     * @param  string $caseFold
     * @return string
     * @throws Exception\LdapException
     */
    public function toString($caseFold = null)
    {
        $caseFold = static::sanitizeCaseFold($caseFold, $this->caseFold);
        return static::implodeDn($this->dn, $caseFold);
    }

    /**
     * Return DN as an array
     *
     * @param  string $caseFold
     * @return array
     */
    public function toArray($caseFold = null)
    {
        $caseFold = static::sanitizeCaseFold($caseFold, $this->caseFold);

        if ($caseFold === self::ATTR_CASEFOLD_NONE) {
            return $this->dn;
        }
        return static::caseFoldDn($this->dn, $caseFold);
    }

    /**
     * Do a case folding on a RDN
     *
     * @param  array  $part
     * @param  string $caseFold
     * @return array
     */
    protected static function caseFoldRdn(array $part, $caseFold)
    {
        switch ($caseFold) {
            case self::ATTR_CASEFOLD_UPPER:
                return array_change_key_case($part, CASE_UPPER);
            case self::ATTR_CASEFOLD_LOWER:
                return array_change_key_case($part, CASE_LOWER);
            case self::ATTR_CASEFOLD_NONE:
            default:
                return $part;
        }
    }

    /**
     * Do a case folding on a DN ort part of it
     *
     * @param  array  $dn
     * @param  string $caseFold
     * @return array
     */
    protected static function caseFoldDn(array $dn, $caseFold)
    {
        $return = array();
        foreach ($dn as $part) {
            $return[] = static::caseFoldRdn($part, $caseFold);
        }
        return $return;
    }

    /**
     * Cast to string representation {@see toString()}
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Required by the ArrayAccess implementation
     *
     * @param  int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        $offset = (int) $offset;
        if ($offset < 0 || $offset >= count($this->dn)) {
            return false;
        }
        return true;
    }

    /**
     * Proxy to {@see get()}
     * Required by the ArrayAccess implementation
     *
     * @param  int $offset
     * @return array
     */
    public function offsetGet($offset)
    {
        return $this->get($offset, 1, null);
    }

    /**
     * Proxy to {@see set()}
     * Required by the ArrayAccess implementation
     *
     * @param int   $offset
     * @param array $value
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Proxy to {@see remove()}
     * Required by the ArrayAccess implementation
     *
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset, 1);
    }

    /**
     * Sets the default case fold
     *
     * @param string $caseFold
     */
    public static function setDefaultCaseFold($caseFold)
    {
        static::$defaultCaseFold = static::sanitizeCaseFold($caseFold, self::ATTR_CASEFOLD_NONE);
    }

    /**
     * Sanitizes the case fold
     *
     * @param  string $caseFold
     * @param  string $default
     * @return string
     */
    protected static function sanitizeCaseFold($caseFold, $default)
    {
        switch ($caseFold) {
            case self::ATTR_CASEFOLD_NONE:
            case self::ATTR_CASEFOLD_UPPER:
            case self::ATTR_CASEFOLD_LOWER:
                return $caseFold;
                break;
            default:
                return $default;
                break;
        }
    }

    /**
     * Escapes a DN value according to RFC 2253
     *
     * Escapes the given VALUES according to RFC 2253 so that they can be safely used in LDAP DNs.
     * The characters ",", "+", """, "\", "<", ">", ";", "#", " = " with a special meaning in RFC 2252
     * are preceded by ba backslash. Control characters with an ASCII code < 32 are represented as \hexpair.
     * Finally all leading and trailing spaces are converted to sequences of \20.
     * @see    Net_LDAP2_Util::escape_dn_value() from Benedikt Hallinger <beni@php.net>
     * @link   http://pear.php.net/package/Net_LDAP2
     * @author Benedikt Hallinger <beni@php.net>
     *
     * @param  string|array $values An array containing the DN values that should be escaped
     * @return array The array $values, but escaped
     */
    public static function escapeValue($values = array())
    {
        if (!is_array($values)) {
            $values = array($values);
        }
        foreach ($values as $key => $val) {
            // Escaping of filter meta characters
            $val = str_replace(
                array('\\', ',', '+', '"', '<', '>', ';', '#', '=',),
                array('\\\\', '\,', '\+', '\"', '\<', '\>', '\;', '\#', '\='), $val
            );
            $val = Converter\Converter::ascToHex32($val);

            // Convert all leading and trailing spaces to sequences of \20.
            if (preg_match('/^(\s*)(.+?)(\s*)$/', $val, $matches)) {
                $val = $matches[2];
                for ($i = 0, $len = strlen($matches[1]); $i < $len; $i++) {
                    $val = '\20' . $val;
                }
                for ($i = 0, $len = strlen($matches[3]); $i < $len; $i++) {
                    $val = $val . '\20';
                }
            }
            if (null === $val) {
                $val = '\0';
            } // apply escaped "null" if string is empty
            $values[$key] = $val;
        }
        return (count($values) == 1) ? $values[0] : $values;
    }

    /**
     * Undoes the conversion done by {@link escapeValue()}.
     *
     * Any escape sequence starting with a baskslash - hexpair or special character -
     * will be transformed back to the corresponding character.
     * @see    Net_LDAP2_Util::escape_dn_value() from Benedikt Hallinger <beni@php.net>
     * @link   http://pear.php.net/package/Net_LDAP2
     * @author Benedikt Hallinger <beni@php.net>
     *
     * @param  string|array $values Array of DN Values
     * @return array Same as $values, but unescaped
     */
    public static function unescapeValue($values = array())
    {
        if (!is_array($values)) {
            $values = array($values);
        }
        foreach ($values as $key => $val) {
            // strip slashes from special chars
            $val          = str_replace(
                array('\\\\', '\,', '\+', '\"', '\<', '\>', '\;', '\#', '\='),
                array('\\', ',', '+', '"', '<', '>', ';', '#', '=',), $val
            );
            $values[$key] = Converter\Converter::hex32ToAsc($val);
        }
        return (count($values) == 1) ? $values[0] : $values;
    }

    /**
     * Creates an array containing all parts of the given DN.
     *
     * Array will be of type
     * array(
     *      array("cn" => "name1", "uid" => "user"),
     *      array("cn" => "name2"),
     *      array("dc" => "example"),
     *      array("dc" => "org")
     * )
     * for a DN of cn=name1+uid=user,cn=name2,dc=example,dc=org.
     *
     * @param  string $dn
     * @param  array  $keys     An optional array to receive DN keys (e.g. CN, OU, DC, ...)
     * @param  array  $vals     An optional array to receive DN values
     * @param  string $caseFold
     * @return array
     * @throws Exception\LdapException
     */
    public static function explodeDn(
        $dn, array &$keys = null, array &$vals = null,
        $caseFold = self::ATTR_CASEFOLD_NONE
    ) {
        $k = array();
        $v = array();
        if (!self::checkDn($dn, $k, $v, $caseFold)) {
            throw new Exception\LdapException(null, 'DN is malformed');
        }
        $ret = array();
        for ($i = 0, $count = count($k); $i < $count; $i++) {
            if (is_array($k[$i]) && is_array($v[$i]) && (count($k[$i]) === count($v[$i]))) {
                $multi = array();
                for ($j = 0; $j < count($k[$i]); $j++) {
                    $key         = $k[$i][$j];
                    $val         = $v[$i][$j];
                    $multi[$key] = $val;
                }
                $ret[] = $multi;
            } elseif (is_string($k[$i]) && is_string($v[$i])) {
                $ret[] = array($k[$i] => $v[$i]);
            }
        }
        if ($keys !== null) {
            $keys = $k;
        }
        if ($vals !== null) {
            $vals = $v;
        }
        return $ret;
    }

    /**
     * @param  string $dn       The DN to parse
     * @param  array  $keys     An optional array to receive DN keys (e.g. CN, OU, DC, ...)
     * @param  array  $vals     An optional array to receive DN values
     * @param  string $caseFold
     * @return bool True if the DN was successfully parsed or false if the string is not a valid DN.
     */
    public static function checkDn(
        $dn, array &$keys = null, array &$vals = null,
        $caseFold = self::ATTR_CASEFOLD_NONE
    ) {
        /* This is a classic state machine parser. Each iteration of the
         * loop processes one character. State 1 collects the key. When equals ( = )
         * is encountered the state changes to 2 where the value is collected
         * until a comma (,) or semicolon (;) is encountered after which we switch back
         * to state 1. If a backslash (\) is encountered, state 3 is used to collect the
         * following character without engaging the logic of other states.
         */
        $key   = null;
        $value = null;
        $slen  = strlen($dn);
        $state = 1;
        $ko    = $vo = 0;
        $multi = false;
        $ka    = array();
        $va    = array();
        for ($di = 0; $di <= $slen; $di++) {
            $ch = ($di == $slen) ? 0 : $dn[$di];
            switch ($state) {
                case 1: // collect key
                    if ($ch === '=') {
                        $key = trim(substr($dn, $ko, $di - $ko));
                        if ($caseFold == self::ATTR_CASEFOLD_LOWER) {
                            $key = strtolower($key);
                        } elseif ($caseFold == self::ATTR_CASEFOLD_UPPER) {
                            $key = strtoupper($key);
                        }
                        if (is_array($multi)) {
                            $keyId = strtolower($key);
                            if (in_array($keyId, $multi)) {
                                return false;
                            }
                            $ka[count($ka) - 1][] = $key;
                            $multi[]              = $keyId;
                        } else {
                            $ka[] = $key;
                        }
                        $state = 2;
                        $vo    = $di + 1;
                    } elseif ($ch === ',' || $ch === ';' || $ch === '+') {
                        return false;
                    }
                    break;
                case 2: // collect value
                    if ($ch === '\\') {
                        $state = 3;
                    } elseif ($ch === ',' || $ch === ';' || $ch === 0 || $ch === '+') {
                        $value = static::unescapeValue(trim(substr($dn, $vo, $di - $vo)));
                        if (is_array($multi)) {
                            $va[count($va) - 1][] = $value;
                        } else {
                            $va[] = $value;
                        }
                        $state = 1;
                        $ko    = $di + 1;
                        if ($ch === '+' && $multi === false) {
                            $lastKey = array_pop($ka);
                            $lastVal = array_pop($va);
                            $ka[]    = array($lastKey);
                            $va[]    = array($lastVal);
                            $multi   = array(strtolower($lastKey));
                        } elseif ($ch === ',' || $ch === ';' || $ch === 0) {
                            $multi = false;
                        }
                    } elseif ($ch === '=') {
                        return false;
                    }
                    break;
                case 3: // escaped
                    $state = 2;
                    break;
            }
        }

        if ($keys !== null) {
            $keys = $ka;
        }
        if ($vals !== null) {
            $vals = $va;
        }

        return ($state === 1 && $ko > 0);
    }

    /**
     * Returns a DN part in the form $attribute = $value
     *
     * This method supports the creation of multi-valued RDNs
     * $part must contain an even number of elements.
     *
     * @param  array  $part
     * @param  string $caseFold
     * @return string
     * @throws Exception\LdapException
     */
    public static function implodeRdn(array $part, $caseFold = null)
    {
        static::assertRdn($part);
        $part     = static::caseFoldRdn($part, $caseFold);
        $rdnParts = array();
        foreach ($part as $key => $value) {
            $value            = static::escapeValue($value);
            $keyId            = strtolower($key);
            $rdnParts[$keyId] = implode('=', array($key, $value));
        }
        ksort($rdnParts, SORT_STRING);

        return implode('+', $rdnParts);
    }

    /**
     * Implodes an array in the form delivered by {@link explodeDn()}
     * to a DN string.
     *
     * $dnArray must be of type
     * array(
     *      array("cn" => "name1", "uid" => "user"),
     *      array("cn" => "name2"),
     *      array("dc" => "example"),
     *      array("dc" => "org")
     * )
     *
     * @param  array  $dnArray
     * @param  string $caseFold
     * @param  string $separator
     * @return string
     * @throws Exception\LdapException
     */
    public static function implodeDn(array $dnArray, $caseFold = null, $separator = ',')
    {
        $parts = array();
        foreach ($dnArray as $p) {
            $parts[] = static::implodeRdn($p, $caseFold);
        }

        return implode($separator, $parts);
    }

    /**
     * Checks if given $childDn is beneath $parentDn subtree.
     *
     * @param  string|Dn $childDn
     * @param  string|Dn $parentDn
     * @return bool
     */
    public static function isChildOf($childDn, $parentDn)
    {
        try {
            $keys = array();
            $vals = array();
            if ($childDn instanceof Dn) {
                $cdn = $childDn->toArray(DN::ATTR_CASEFOLD_LOWER);
            } else {
                $cdn = static::explodeDn($childDn, $keys, $vals, DN::ATTR_CASEFOLD_LOWER);
            }
            if ($parentDn instanceof Dn) {
                $pdn = $parentDn->toArray(DN::ATTR_CASEFOLD_LOWER);
            } else {
                $pdn = static::explodeDn($parentDn, $keys, $vals, DN::ATTR_CASEFOLD_LOWER);
            }
        } catch (Exception\LdapException $e) {
            return false;
        }

        $startIndex = count($cdn) - count($pdn);
        if ($startIndex < 0) {
            return false;
        }
        for ($i = 0, $count = count($pdn); $i < $count; $i++) {
            if ($cdn[$i + $startIndex] != $pdn[$i]) {
                return false;
            }
        }
        return true;
    }
}
