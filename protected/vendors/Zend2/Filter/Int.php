<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Filter;

class Int extends AbstractFilter
{
    /**
     * Defined by Zend\Filter\FilterInterface
     *
     * Returns (int) $value
     *
     * If the value provided is non-scalar, the value will remain unfiltered
     * and an E_USER_WARNING will be raised indicating it's unfilterable.
     *
     * @param  string $value
     * @return int|mixed
     */
    public function filter($value)
    {
        if (null === $value) {
            return null;
        }

        if (!is_scalar($value)) {
            trigger_error(
                sprintf(
                    '%s expects parameter to be scalar, "%s" given; cannot filter',
                    __METHOD__,
                    (is_object($value) ? get_class($value) : gettype($value))
                ),
                E_USER_WARNING
            );
            return $value;
        }

        return (int) ((string) $value);
    }
}
