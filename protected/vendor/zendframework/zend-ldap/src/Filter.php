<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Ldap;

/**
 * Zend\Ldap\Filter.
 */
class Filter extends Filter\StringFilter
{
    const TYPE_EQUALS         = '=';
    const TYPE_GREATER        = '>';
    const TYPE_GREATEROREQUAL = '>=';
    const TYPE_LESS           = '<';
    const TYPE_LESSOREQUAL    = '<=';
    const TYPE_APPROX         = '~=';

    /**
     * Creates an 'equals' filter.
     * (attr=value)
     *
     * @param  string $attr
     * @param  string $value
     * @return Filter
     */
    public static function equals($attr, $value)
    {
        return new static($attr, $value, self::TYPE_EQUALS, null, null);
    }

    /**
     * Creates a 'begins with' filter.
     * (attr=value*)
     *
     * @param  string $attr
     * @param  string $value
     * @return Filter
     */
    public static function begins($attr, $value)
    {
        return new static($attr, $value, self::TYPE_EQUALS, null, '*');
    }

    /**
     * Creates an 'ends with' filter.
     * (attr=*value)
     *
     * @param  string $attr
     * @param  string $value
     * @return Filter
     */
    public static function ends($attr, $value)
    {
        return new static($attr, $value, self::TYPE_EQUALS, '*', null);
    }

    /**
     * Creates a 'contains' filter.
     * (attr=*value*)
     *
     * @param  string $attr
     * @param  string $value
     * @return Filter
     */
    public static function contains($attr, $value)
    {
        return new static($attr, $value, self::TYPE_EQUALS, '*', '*');
    }

    /**
     * Creates a 'greater' filter.
     * (attr>value)
     *
     * @param  string $attr
     * @param  string $value
     * @return Filter
     */
    public static function greater($attr, $value)
    {
        return new static($attr, $value, self::TYPE_GREATER, null, null);
    }

    /**
     * Creates a 'greater or equal' filter.
     * (attr>=value)
     *
     * @param  string $attr
     * @param  string $value
     * @return Filter
     */
    public static function greaterOrEqual($attr, $value)
    {
        return new static($attr, $value, self::TYPE_GREATEROREQUAL, null, null);
    }

    /**
     * Creates a 'less' filter.
     * (attr<value)
     *
     * @param  string $attr
     * @param  string $value
     * @return Filter
     */
    public static function less($attr, $value)
    {
        return new static($attr, $value, self::TYPE_LESS, null, null);
    }

    /**
     * Creates an 'less or equal' filter.
     * (attr<=value)
     *
     * @param  string $attr
     * @param  string $value
     * @return Filter
     */
    public static function lessOrEqual($attr, $value)
    {
        return new static($attr, $value, self::TYPE_LESSOREQUAL, null, null);
    }

    /**
     * Creates an 'approx' filter.
     * (attr~=value)
     *
     * @param  string $attr
     * @param  string $value
     * @return Filter
     */
    public static function approx($attr, $value)
    {
        return new static($attr, $value, self::TYPE_APPROX, null, null);
    }

    /**
     * Creates an 'any' filter.
     * (attr=*)
     *
     * @param  string $attr
     * @return Filter
     */
    public static function any($attr)
    {
        return new static($attr, '', self::TYPE_EQUALS, '*', null);
    }

    /**
     * Creates a simple custom string filter.
     *
     * @param  string $filter
     * @return Filter\StringFilter
     */
    public static function string($filter)
    {
        return new Filter\StringFilter($filter);
    }

    /**
     * Creates a simple string filter to be used with a mask.
     *
     * @param string $mask
     * @param string $value
     * @return Filter\MaskFilter
     */
    public static function mask($mask, $value)
    {
        return new Filter\MaskFilter($mask, $value);
    }

    /**
     * Creates an 'and' filter.
     *
     * @param  Filter\AbstractFilter $filter,...
     * @return Filter\AndFilter
     */
    public static function andFilter($filter)
    {
        return new Filter\AndFilter(func_get_args());
    }

    /**
     * Creates an 'or' filter.
     *
     * @param  Filter\AbstractFilter $filter,...
     * @return Filter\OrFilter
     */
    public static function orFilter($filter)
    {
        return new Filter\OrFilter(func_get_args());
    }

    /**
     * Create a filter string.
     *
     * @param  string $attr
     * @param  string $value
     * @param  string $filtertype
     * @param  string $prepend
     * @param  string $append
     * @return string
     */
    private static function createFilterString($attr, $value, $filtertype, $prepend = null, $append = null)
    {
        $str = $attr . $filtertype;
        if ($prepend !== null) {
            $str .= $prepend;
        }
        $str .= static::escapeValue($value);
        if ($append !== null) {
            $str .= $append;
        }
        return $str;
    }

    /**
     * Creates a new Zend\Ldap\Filter.
     *
     * @param string $attr
     * @param string $value
     * @param string $filtertype
     * @param string $prepend
     * @param string $append
     */
    public function __construct($attr, $value, $filtertype, $prepend = null, $append = null)
    {
        $filter = static::createFilterString($attr, $value, $filtertype, $prepend, $append);
        parent::__construct($filter);
    }
}
