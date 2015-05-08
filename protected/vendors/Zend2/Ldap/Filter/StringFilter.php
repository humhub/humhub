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
 * Zend\Ldap\Filter\StringFilter provides a simple custom string filter.
 */
class StringFilter extends AbstractFilter
{
    /**
     * The filter.
     *
     * @var string
     */
    protected $filter;

    /**
     * Creates a Zend\Ldap\Filter\StringFilter.
     *
     * @param string $filter
     */
    public function __construct($filter)
    {
        $this->filter = $filter;
    }

    /**
     * Returns a string representation of the filter.
     *
     * @return string
     */
    public function toString()
    {
        return '(' . $this->filter . ')';
    }
}
