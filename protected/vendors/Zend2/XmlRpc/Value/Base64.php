<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\XmlRpc\Value;

class Base64 extends AbstractScalar
{

    /**
     * Set the value of a base64 native type
     * We keep this value in base64 encoding
     *
     * @param string $value
     * @param bool $alreadyEncoded If set, it means that the given string is already base64 encoded
     */
    public function __construct($value, $alreadyEncoded = false)
    {
        $this->type = self::XMLRPC_TYPE_BASE64;

        $value = (string) $value;    // Make sure this value is string
        if (!$alreadyEncoded) {
            $value = base64_encode($value);     // We encode it in base64
        }
        $this->value = $value;
    }

    /**
     * Return the value of this object, convert the XML-RPC native base64 value into a PHP string
     * We return this value decoded (a normal string)
     *
     * @return string
     */
    public function getValue()
    {
        return base64_decode($this->value);
    }
}
