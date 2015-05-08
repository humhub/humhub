<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\XmlRpc\Value;

class Nil extends AbstractScalar
{

    /**
     * Set the value of a nil native type
     *
     */
    public function __construct()
    {
        $this->type = self::XMLRPC_TYPE_NIL;
        $this->value = null;
    }

    /**
     * Return the value of this object, convert the XML-RPC native nill value into a PHP NULL
     *
     * @return null
     */
    public function getValue()
    {
        return null;
    }
}
