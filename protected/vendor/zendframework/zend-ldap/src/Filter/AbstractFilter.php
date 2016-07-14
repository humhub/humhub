<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Ldap\Filter;

use Zend\Ldap\Converter\Converter;

/**
 * Zend\Ldap\Filter\AbstractFilter provides a base implementation for filters.
 */
abstract class AbstractFilter
{
    /**
     * Returns a string representation of the filter.
     *
     * @return string
     */
    abstract public function toString();

    /**
     * Returns a string representation of the filter.
     * @see toString()
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Negates the filter.
     *
     * @return AbstractFilter
     */
    public function negate()
    {
        return new NotFilter($this);
    }

    /**
     * Creates an 'and' filter.
     *
     * @param  AbstractFilter $filter,...
     * @return AndFilter
     */
    public function addAnd($filter)
    {
        $fa   = func_get_args();
        $args = array_merge(array($this), $fa);
        return new AndFilter($args);
    }

    /**
     * Creates an 'or' filter.
     *
     * @param  AbstractFilter $filter,...
     * @return OrFilter
     */
    public function addOr($filter)
    {
        $fa   = func_get_args();
        $args = array_merge(array($this), $fa);
        return new OrFilter($args);
    }

    /**
     * Escapes the given VALUES according to RFC 2254 so that they can be safely used in LDAP filters.
     *
     * Any control characters with an ACII code < 32 as well as the characters with special meaning in
     * LDAP filters "*", "(", ")", and "\" (the backslash) are converted into the representation of a
     * backslash followed by two hex digits representing the hexadecimal value of the character.
     * @see    Net_LDAP2_Util::escape_filter_value() from Benedikt Hallinger <beni@php.net>
     * @link   http://pear.php.net/package/Net_LDAP2
     * @author Benedikt Hallinger <beni@php.net>
     *
     * @param  string|array $values Array of values to escape
     * @return array Array $values, but escaped
     */
    public static function escapeValue($values = array())
    {
        if (!is_array($values)) {
            $values = array($values);
        }
        foreach ($values as $key => $val) {
            // Escaping of filter meta characters
            $val = str_replace(array('\\', '*', '(', ')'), array('\5c', '\2a', '\28', '\29'), $val);
            // ASCII < 32 escaping
            $val = Converter::ascToHex32($val);
            if (null === $val) {
                $val = '\0'; // apply escaped "null" if string is empty
            }
            $values[$key] = $val;
        }
        return (count($values) == 1) ? $values[0] : $values;
    }

    /**
     * Undoes the conversion done by {@link escapeValue()}.
     *
     * Converts any sequences of a backslash followed by two hex digits into the corresponding character.
     * @see    Net_LDAP2_Util::escape_filter_value() from Benedikt Hallinger <beni@php.net>
     * @link   http://pear.php.net/package/Net_LDAP2
     * @author Benedikt Hallinger <beni@php.net>
     *
     * @param  string|array $values Array of values to escape
     * @return array Array $values, but unescaped
     */
    public static function unescapeValue($values = array())
    {
        if (!is_array($values)) {
            $values = array($values);
        }
        foreach ($values as $key => $value) {
            // Translate hex code into ascii
            $values[$key] = Converter::hex32ToAsc($value);
        }
        return (count($values) == 1) ? $values[0] : $values;
    }
}
