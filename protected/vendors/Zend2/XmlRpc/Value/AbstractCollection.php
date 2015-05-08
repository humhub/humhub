<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\XmlRpc\Value;

use Zend\XmlRpc\AbstractValue;

abstract class AbstractCollection extends AbstractValue
{

    /**
     * Set the value of a collection type (array and struct) native types
     *
     * @param array $value
     */
    public function __construct($value)
    {
        $values = (array) $value;   // Make sure that the value is an array
        foreach ($values as $key => $value) {
            // If the elements of the given array are not Zend\XmlRpc\Value objects,
            // we need to convert them as such (using auto-detection from PHP value)
            if (!$value instanceof parent) {
                $value = static::getXmlRpcValue($value, self::AUTO_DETECT_TYPE);
            }
            $this->value[$key] = $value;
        }
    }


    /**
     * Return the value of this object, convert the XML-RPC native collection values into a PHP array
     *
     * @return array
     */
    public function getValue()
    {
        $values = (array) $this->value;
        foreach ($values as $key => $value) {
            $values[$key] = $value->getValue();
        }
        return $values;
    }
}
