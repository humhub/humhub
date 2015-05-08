<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\XmlRpc;

use DateTime;

/**
 * Represent a native XML-RPC value entity, used as parameters for the methods
 * called by the Zend\XmlRpc\Client object and as the return value for those calls.
 *
 * This object as a very important static function Zend\XmlRpc\Value::getXmlRpcValue, this
 * function acts likes a factory for the Zend\XmlRpc\Value objects
 *
 * Using this function, users/Zend\XmlRpc\Client object can create the Zend\XmlRpc\Value objects
 * from PHP variables, XML string or by specifying the exact XML-RPC native type
 */
abstract class AbstractValue
{
    /**
     * The native XML-RPC representation of this object's value
     *
     * If the native type of this object is array or struct, this will be an array
     * of Value objects
     */
    protected $value;

    /**
     * The native XML-RPC type of this object
     * One of the XMLRPC_TYPE_* constants
     */
    protected $type;

    /**
     * XML code representation of this object (will be calculated only once)
     */
    protected $xml;

    /**
     * @var \Zend\XmlRpc\Generator\GeneratorInterface
     */
    protected static $generator;

    /**
     * Specify that the XML-RPC native type will be auto detected from a PHP variable type
     */
    const AUTO_DETECT_TYPE = 'auto_detect';

    /**
     * Specify that the XML-RPC value will be parsed out from a given XML code
     */
    const XML_STRING = 'xml';

    /**
     * All the XML-RPC native types
     */
    const XMLRPC_TYPE_I4        = 'i4';
    const XMLRPC_TYPE_INTEGER   = 'int';
    const XMLRPC_TYPE_I8        = 'i8';
    const XMLRPC_TYPE_APACHEI8  = 'ex:i8';
    const XMLRPC_TYPE_DOUBLE    = 'double';
    const XMLRPC_TYPE_BOOLEAN   = 'boolean';
    const XMLRPC_TYPE_STRING    = 'string';
    const XMLRPC_TYPE_DATETIME  = 'dateTime.iso8601';
    const XMLRPC_TYPE_BASE64    = 'base64';
    const XMLRPC_TYPE_ARRAY     = 'array';
    const XMLRPC_TYPE_STRUCT    = 'struct';
    const XMLRPC_TYPE_NIL       = 'nil';
    const XMLRPC_TYPE_APACHENIL = 'ex:nil';

    /**
     * Get the native XML-RPC type (the type is one of the Value::XMLRPC_TYPE_* constants)
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get XML generator instance
     *
     * @return \Zend\XmlRpc\Generator\GeneratorInterface
     */
    public static function getGenerator()
    {
        if (!static::$generator) {
            if (extension_loaded('xmlwriter')) {
                static::$generator = new Generator\XmlWriter();
            } else {
                static::$generator = new Generator\DomDocument();
            }
        }

        return static::$generator;
    }

    /**
     * Sets XML generator instance
     *
     * @param  null|Generator\GeneratorInterface $generator
     * @return void
     */
    public static function setGenerator(Generator\GeneratorInterface $generator = null)
    {
        static::$generator = $generator;
    }

    /**
     * Changes the encoding of the generator
     *
     * @param string $encoding
     * @return void
     */
    public static function setEncoding($encoding)
    {
        $generator    = static::getGenerator();
        $newGenerator = new $generator($encoding);
        static::setGenerator($newGenerator);
    }

    /**
     * Return the value of this object, convert the XML-RPC native value into a PHP variable
     *
     * @return mixed
     */
    abstract public function getValue();


    /**
     * Return the XML code that represent a native MXL-RPC value
     *
     * @return string
     */
    public function saveXml()
    {
        if (!$this->xml) {
            $this->generateXml();
            $this->xml = (string) $this->getGenerator();
        }
        return $this->xml;
    }

    /**
     * Generate XML code that represent a native XML/RPC value
     *
     * @return void
     */
    public function generateXml()
    {
        $this->_generateXml();
    }

    /**
     * Creates a Value* object, representing a native XML-RPC value
     * A XmlRpcValue object can be created in 3 ways:
     * 1. Autodetecting the native type out of a PHP variable
     *    (if $type is not set or equal to Value::AUTO_DETECT_TYPE)
     * 2. By specifying the native type ($type is one of the Value::XMLRPC_TYPE_* constants)
     * 3. From a XML string ($type is set to Value::XML_STRING)
     *
     * By default the value type is autodetected according to it's PHP type
     *
     * @param  mixed $value
     * @param  Zend\XmlRpc\Value::constant $type
     * @throws Exception\ValueException
     * @return AbstractValue
     */
    public static function getXmlRpcValue($value, $type = self::AUTO_DETECT_TYPE)
    {
        switch ($type) {
            case self::AUTO_DETECT_TYPE:
                // Auto detect the XML-RPC native type from the PHP type of $value
                return static::_phpVarToNativeXmlRpc($value);

            case self::XML_STRING:
                // Parse the XML string given in $value and get the XML-RPC value in it
                return static::_xmlStringToNativeXmlRpc($value);

            case self::XMLRPC_TYPE_I4:
                // fall through to the next case
            case self::XMLRPC_TYPE_INTEGER:
                return new Value\Integer($value);

            case self::XMLRPC_TYPE_I8:
                // fall through to the next case
            case self::XMLRPC_TYPE_APACHEI8:
                return new Value\BigInteger($value);

            case self::XMLRPC_TYPE_DOUBLE:
                return new Value\Double($value);

            case self::XMLRPC_TYPE_BOOLEAN:
                return new Value\Boolean($value);

            case self::XMLRPC_TYPE_STRING:
                return new Value\String($value);

            case self::XMLRPC_TYPE_BASE64:
                return new Value\Base64($value);

            case self::XMLRPC_TYPE_NIL:
                // fall through to the next case
            case self::XMLRPC_TYPE_APACHENIL:
                return new Value\Nil();

            case self::XMLRPC_TYPE_DATETIME:
                return new Value\DateTime($value);

            case self::XMLRPC_TYPE_ARRAY:
                return new Value\ArrayValue($value);

            case self::XMLRPC_TYPE_STRUCT:
                return new Value\Struct($value);

            default:
                throw new Exception\ValueException('Given type is not a '. __CLASS__ .' constant');
        }
    }

    /**
     * Get XML-RPC type for a PHP native variable
     *
     * @static
     * @param mixed $value
     * @throws Exception\InvalidArgumentException
     * @return string
     */
    public static function getXmlRpcTypeByValue($value)
    {
        if (is_object($value)) {
            if ($value instanceof AbstractValue) {
                return $value->getType();
            } elseif ($value instanceof DateTime) {
                return self::XMLRPC_TYPE_DATETIME;
            }
            return static::getXmlRpcTypeByValue(get_object_vars($value));
        } elseif (is_array($value)) {
            if (!empty($value) && is_array($value) && (array_keys($value) !== range(0, count($value) - 1))) {
                return self::XMLRPC_TYPE_STRUCT;
            }
            return self::XMLRPC_TYPE_ARRAY;
        } elseif (is_int($value)) {
            return ($value > PHP_INT_MAX) ? self::XMLRPC_TYPE_I8 : self::XMLRPC_TYPE_INTEGER;
        } elseif (is_double($value)) {
            return self::XMLRPC_TYPE_DOUBLE;
        } elseif (is_bool($value)) {
            return self::XMLRPC_TYPE_BOOLEAN;
        } elseif (null === $value) {
            return self::XMLRPC_TYPE_NIL;
        } elseif (is_string($value)) {
            return self::XMLRPC_TYPE_STRING;
        }
        throw new Exception\InvalidArgumentException(sprintf(
            'No matching XMLRPC type found for php type %s.',
            gettype($value)
        ));
    }

    /**
     * Transform a PHP native variable into a XML-RPC native value
     *
     * @param mixed $value The PHP variable for conversion
     *
     * @throws Exception\InvalidArgumentException
     * @return AbstractValue
     * @static
     */
    protected static function _phpVarToNativeXmlRpc($value)
    {
        // @see http://framework.zend.com/issues/browse/ZF-8623
        if ($value instanceof AbstractValue) {
            return $value;
        }

        switch (static::getXmlRpcTypeByValue($value)) {
            case self::XMLRPC_TYPE_DATETIME:
                return new Value\DateTime($value);

            case self::XMLRPC_TYPE_ARRAY:
                return new Value\ArrayValue($value);

            case self::XMLRPC_TYPE_STRUCT:
                return new Value\Struct($value);

            case self::XMLRPC_TYPE_INTEGER:
                return new Value\Integer($value);

            case self::XMLRPC_TYPE_DOUBLE:
                return new Value\Double($value);

            case self::XMLRPC_TYPE_BOOLEAN:
                return new Value\Boolean($value);

            case self::XMLRPC_TYPE_NIL:
                return new Value\Nil;

            case self::XMLRPC_TYPE_STRING:
                // Fall through to the next case
            default:
                // If type isn't identified (or identified as string), it treated as string
                return new Value\String($value);
        }
    }

    /**
     * Transform an XML string into a XML-RPC native value
     *
     * @param string|\SimpleXMLElement $xml A SimpleXMLElement object represent the XML string
     * It can be also a valid XML string for conversion
     *
     * @throws Exception\ValueException
     * @return \Zend\XmlRpc\AbstractValue
     * @static
     */
    protected static function _xmlStringToNativeXmlRpc($xml)
    {
        static::_createSimpleXMLElement($xml);

        static::_extractTypeAndValue($xml, $type, $value);

        switch ($type) {
            // All valid and known XML-RPC native values
            case self::XMLRPC_TYPE_I4:
                // Fall through to the next case
            case self::XMLRPC_TYPE_INTEGER:
                $xmlrpcValue = new Value\Integer($value);
                break;
            case self::XMLRPC_TYPE_APACHEI8:
                // Fall through to the next case
            case self::XMLRPC_TYPE_I8:
                $xmlrpcValue = new Value\BigInteger($value);
                break;
            case self::XMLRPC_TYPE_DOUBLE:
                $xmlrpcValue = new Value\Double($value);
                break;
            case self::XMLRPC_TYPE_BOOLEAN:
                $xmlrpcValue = new Value\Boolean($value);
                break;
            case self::XMLRPC_TYPE_STRING:
                $xmlrpcValue = new Value\String($value);
                break;
            case self::XMLRPC_TYPE_DATETIME:  // The value should already be in a iso8601 format
                $xmlrpcValue = new Value\DateTime($value);
                break;
            case self::XMLRPC_TYPE_BASE64:    // The value should already be base64 encoded
                $xmlrpcValue = new Value\Base64($value, true);
                break;
            case self::XMLRPC_TYPE_NIL:
                // Fall through to the next case
            case self::XMLRPC_TYPE_APACHENIL:
                // The value should always be NULL
                $xmlrpcValue = new Value\Nil();
                break;
            case self::XMLRPC_TYPE_ARRAY:
                // PHP 5.2.4 introduced a regression in how empty($xml->value)
                // returns; need to look for the item specifically
                $data = null;
                foreach ($value->children() as $key => $value) {
                    if ('data' == $key) {
                        $data = $value;
                        break;
                    }
                }

                if (null === $data) {
                    throw new Exception\ValueException('Invalid XML for XML-RPC native '. self::XMLRPC_TYPE_ARRAY .' type: ARRAY tag must contain DATA tag');
                }
                $values = array();
                // Parse all the elements of the array from the XML string
                // (simple xml element) to Value objects
                foreach ($data->value as $element) {
                    $values[] = static::_xmlStringToNativeXmlRpc($element);
                }
                $xmlrpcValue = new Value\ArrayValue($values);
                break;
            case self::XMLRPC_TYPE_STRUCT:
                $values = array();
                // Parse all the members of the struct from the XML string
                // (simple xml element) to Value objects
                foreach ($value->member as $member) {
                    // @todo? If a member doesn't have a <value> tag, we don't add it to the struct
                    // Maybe we want to throw an exception here ?
                    if (!isset($member->value) or !isset($member->name)) {
                        continue;
                        //throw new Value_Exception('Member of the '. self::XMLRPC_TYPE_STRUCT .' XML-RPC native type must contain a VALUE tag');
                    }
                    $values[(string) $member->name] = static::_xmlStringToNativeXmlRpc($member->value);
                }
                $xmlrpcValue = new Value\Struct($values);
                break;
            default:
                throw new Exception\ValueException('Value type \''. $type .'\' parsed from the XML string is not a known XML-RPC native type');
                break;
        }
        $xmlrpcValue->_setXML($xml->asXML());

        return $xmlrpcValue;
    }

    protected static function _createSimpleXMLElement(&$xml)
    {
        if ($xml instanceof \SimpleXMLElement) {
            return;
        }

        try {
            $xml = new \SimpleXMLElement($xml);
        } catch (\Exception $e) {
            // The given string is not a valid XML
            throw new Exception\ValueException('Failed to create XML-RPC value from XML string: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Extract XML/RPC type and value from SimpleXMLElement object
     *
     * @param \SimpleXMLElement $xml
     * @param string &$type Type bind variable
     * @param string &$value Value bind variable
     * @return void
     */
    protected static function _extractTypeAndValue(\SimpleXMLElement $xml, &$type, &$value)
    {
        list($type, $value) = each($xml);
        if (!$type and $value === null) {
            $namespaces = array('ex' => 'http://ws.apache.org/xmlrpc/namespaces/extensions');
            foreach ($namespaces as $namespaceName => $namespaceUri) {
                $namespaceXml = $xml->children($namespaceUri);
                list($type, $value) = each($namespaceXml);
                if ($type !== null) {
                    $type = $namespaceName . ':' . $type;
                    break;
                }
            }
        }

        // If no type was specified, the default is string
        if (!$type) {
            $type = self::XMLRPC_TYPE_STRING;
        }
    }

    /**
     * @param $xml
     * @return void
     */
    protected function _setXML($xml)
    {
        $this->xml = $this->getGenerator()->stripDeclaration($xml);
    }
}
