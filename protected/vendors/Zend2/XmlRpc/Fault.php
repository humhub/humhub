<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\XmlRpc;

use SimpleXMLElement;

/**
 * XMLRPC Faults
 *
 * Container for XMLRPC faults, containing both a code and a message;
 * additionally, has methods for determining if an XML response is an XMLRPC
 * fault, as well as generating the XML for an XMLRPC fault response.
 *
 * To allow method chaining, you may only use the {@link getInstance()} factory
 * to instantiate a Zend\XmlRpc\Server\Fault.
 */
class Fault
{
    /**
     * Fault code
     * @var int
     */
    protected $code;

    /**
     * Fault character encoding
     * @var string
     */
    protected $encoding = 'UTF-8';

    /**
     * Fault message
     * @var string
     */
    protected $message;

    /**
     * Internal fault codes => messages
     * @var array
     */
    protected $internal = array(
        404 => 'Unknown Error',

        // 610 - 619 reflection errors
        610 => 'Invalid method class',
        611 => 'Unable to attach function or callback; not callable',
        612 => 'Unable to load array; not an array',
        613 => 'One or more method records are corrupt or otherwise unusable',

        // 620 - 629 dispatch errors
        620 => 'Method does not exist',
        621 => 'Error instantiating class to invoke method',
        622 => 'Method missing implementation',
        623 => 'Calling parameters do not match signature',

        // 630 - 639 request errors
        630 => 'Unable to read request',
        631 => 'Failed to parse request',
        632 => 'Invalid request, no method passed; request must contain a \'methodName\' tag',
        633 => 'Param must contain a value',
        634 => 'Invalid method name',
        635 => 'Invalid XML provided to request',
        636 => 'Error creating xmlrpc value',

        // 640 - 649 system.* errors
        640 => 'Method does not exist',

        // 650 - 659 response errors
        650 => 'Invalid XML provided for response',
        651 => 'Failed to parse response',
        652 => 'Invalid response',
        653 => 'Invalid XMLRPC value in response',
    );

    /**
     * Constructor
     *
     */
    public function __construct($code = 404, $message = '')
    {
        $this->setCode($code);
        $code = $this->getCode();

        if (empty($message) && isset($this->internal[$code])) {
            $message = $this->internal[$code];
        } elseif (empty($message)) {
            $message = 'Unknown error';
        }
        $this->setMessage($message);
    }

    /**
     * Set the fault code
     *
     * @param int $code
     * @return Fault
     */
    public function setCode($code)
    {
        $this->code = (int) $code;
        return $this;
    }

    /**
     * Return fault code
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Retrieve fault message
     *
     * @param string
     * @return Fault
     */
    public function setMessage($message)
    {
        $this->message = (string) $message;
        return $this;
    }

    /**
     * Retrieve fault message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set encoding to use in fault response
     *
     * @param string $encoding
     * @return Fault
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
        AbstractValue::setEncoding($encoding);
        return $this;
    }

    /**
     * Retrieve current fault encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Load an XMLRPC fault from XML
     *
     * @param string $fault
     * @return bool Returns true if successfully loaded fault response, false
     * if response was not a fault response
     * @throws Exception\ExceptionInterface if no or faulty XML provided, or if fault
     * response does not contain either code or message
     */
    public function loadXml($fault)
    {
        if (!is_string($fault)) {
            throw new Exception\InvalidArgumentException('Invalid XML provided to fault');
        }

        $xmlErrorsFlag = libxml_use_internal_errors(true);
        try {
            $xml = new SimpleXMLElement($fault);
        } catch (\Exception $e) {
            // Not valid XML
            throw new Exception\InvalidArgumentException('Failed to parse XML fault: ' .  $e->getMessage(), 500, $e);
        }
        if (!$xml instanceof SimpleXMLElement) {
            $errors = libxml_get_errors();
            $errors = array_reduce($errors, function ($result, $item) {
                if (empty($result)) {
                    return $item->message;
                }
                return $result . '; ' . $item->message;
            }, '');
            libxml_use_internal_errors($xmlErrorsFlag);
            throw new Exception\InvalidArgumentException('Failed to parse XML fault: ' . $errors, 500);
        }
        libxml_use_internal_errors($xmlErrorsFlag);

        // Check for fault
        if (!$xml->fault) {
            // Not a fault
            return false;
        }

        if (!$xml->fault->value->struct) {
            // not a proper fault
            throw new Exception\InvalidArgumentException('Invalid fault structure', 500);
        }

        $structXml = $xml->fault->value->asXML();
        $struct    = AbstractValue::getXmlRpcValue($structXml, AbstractValue::XML_STRING);
        $struct    = $struct->getValue();

        if (isset($struct['faultCode'])) {
            $code = $struct['faultCode'];
        }
        if (isset($struct['faultString'])) {
            $message = $struct['faultString'];
        }

        if (empty($code) && empty($message)) {
            throw new Exception\InvalidArgumentException('Fault code and string required');
        }

        if (empty($code)) {
            $code = '404';
        }

        if (empty($message)) {
            if (isset($this->internal[$code])) {
                $message = $this->internal[$code];
            } else {
                $message = 'Unknown Error';
            }
        }

        $this->setCode($code);
        $this->setMessage($message);

        return true;
    }

    /**
     * Determine if an XML response is an XMLRPC fault
     *
     * @param string $xml
     * @return bool
     */
    public static function isFault($xml)
    {
        $fault = new static();
        try {
            $isFault = $fault->loadXml($xml);
        } catch (Exception\ExceptionInterface $e) {
            $isFault = false;
        }

        return $isFault;
    }

    /**
     * Serialize fault to XML
     *
     * @return string
     */
    public function saveXml()
    {
        // Create fault value
        $faultStruct = array(
            'faultCode'   => $this->getCode(),
            'faultString' => $this->getMessage()
        );
        $value = AbstractValue::getXmlRpcValue($faultStruct);

        $generator = AbstractValue::getGenerator();
        $generator->openElement('methodResponse')
                  ->openElement('fault');
        $value->generateXml();
        $generator->closeElement('fault')
                  ->closeElement('methodResponse');

        return $generator->flush();
    }

    /**
     * Return XML fault response
     *
     * @return string
     */
    public function __toString()
    {
        return $this->saveXML();
    }
}
