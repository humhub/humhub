<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\XmlRpc\Value;

use Zend\Math\BigInteger\BigInteger as BigIntegerMath;

class BigInteger extends Integer
{
    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = BigIntegerMath::factory()->init($value, 10);
        $this->type  = self::XMLRPC_TYPE_I8;
    }

    /**
     * Return bigint value object
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
