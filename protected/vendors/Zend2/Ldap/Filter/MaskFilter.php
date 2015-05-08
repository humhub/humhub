<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Ldap\Filter;

/**
 * Zend\Ldap\Filter\MaskFilter provides a simple string filter to be used with a mask.
 */
class MaskFilter extends StringFilter
{
    /**
     * Creates a Zend\Ldap\Filter\MaskFilter.
     *
     * @param string $mask
     * @param string $value,...
     */
    public function __construct($mask, $value)
    {
        $args = func_get_args();
        array_shift($args);
        for ($i = 0; $i < count($args); $i++) {
            $args[$i] = static::escapeValue($args[$i]);
        }
        $filter = vsprintf($mask, $args);
        parent::__construct($filter);
    }

    /**
     * Returns a string representation of the filter.
     *
     * @return string
     */
    public function toString()
    {
        return $this->filter;
    }
}
