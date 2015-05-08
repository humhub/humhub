<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Ldap\Filter;

use Zend\Ldap\Filter\Exception;

/**
 * Zend\Ldap\Filter\AbstractLogicalFilter provides a base implementation for a grouping filter.
 */
abstract class AbstractLogicalFilter extends AbstractFilter
{
    const TYPE_AND = '&';
    const TYPE_OR  = '|';

    /**
     * All the sub-filters for this grouping filter.
     *
     * @var array
     */
    private $subfilters;

    /**
     * The grouping symbol.
     *
     * @var string
     */
    private $symbol;

    /**
     * Creates a new grouping filter.
     *
     * @param array  $subfilters
     * @param string $symbol
     * @throws Exception\FilterException
     */
    protected function __construct(array $subfilters, $symbol)
    {
        foreach ($subfilters as $key => $s) {
            if (is_string($s)) {
                $subfilters[$key] = new StringFilter($s);
            } elseif (!($s instanceof AbstractFilter)) {
                throw new Exception\FilterException('Only strings or Zend\Ldap\Filter\AbstractFilter allowed.');
            }
        }
        $this->subfilters = $subfilters;
        $this->symbol     = $symbol;
    }

    /**
     * Adds a filter to this grouping filter.
     *
     * @param  AbstractFilter $filter
     * @return AbstractLogicalFilter
     */
    public function addFilter(AbstractFilter $filter)
    {
        $new               = clone $this;
        $new->subfilters[] = $filter;
        return $new;
    }

    /**
     * Returns a string representation of the filter.
     *
     * @return string
     */
    public function toString()
    {
        $return = '(' . $this->symbol;
        foreach ($this->subfilters as $sub) {
            $return .= $sub->toString();
        }
        $return .= ')';
        return $return;
    }
}
