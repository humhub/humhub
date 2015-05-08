<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\XmlRpc;

use DOMDocument;
use SimpleXMLElement;
use Zend\Stdlib\ErrorHandler;

/**
 * XmlRpc Request object
 *
 * Encapsulates an XmlRpc request, holding the method call and all parameters.
 * Provides accessors for these, as well as the ability to load from XML and to
 * create the XML request string.
 *
 * Additionally, if errors occur setting the method or parsing XML, a fault is
 * generated and stored in {@link $fault}; developers may check for it using
 * {@link isFault()} and {@link getFault()}.
 */
class Request
{
    /**
     * Request character encoding
     * @var string
     */
    protected $encoding = 'UTF-8';

    /**
     * Method to call
     * @var string
     */
    protected $method;

    /**
     * XML request
     * @var string
     */
    protected $xml;

    /**
     * Method parameters
     * @var array
     */
    protected $params = array();

    /**
     * Fault object, if any
     * @var \Zend\XmlRpc\Fault
     */
    protected $fault = null;

    /**
     * XML-RPC type for each param
     * @var array
     */
    protected $types = array();

    /**
     * XML-RPC request params
     * @var array
     */
    protected $xmlRpcParams = array();

    /**
     * Create a new XML-RPC request
     *
     * @param string $method (optional)
     * @param array $params  (optional)
     */
    public function __construct($method = null, $params = null)
    {
        if ($method !== null) {
            $this->setMethod($method);
        }

        if ($params !== null) {
            $this->setParams($params);
        }
    }


    /**
     * Set encoding to use in request
     *
     * @param string $encoding
     * @return \Zend\XmlRpc\Request
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
        AbstractValue::setEncoding($encoding);
        return $this;
    }

    /**
     * Retrieve current request encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Set method to call
     *
     * @param string $method
     * @return bool Returns true on success, false if method name is invalid
     */
    public function setMethod($method)
    {
        if (!is_string($method) || !preg_match('/^[a-z0-9_.:\\\\\/]+$/i', $method)) {
            $this->fault = new Fault(634, 'Invalid method name ("' . $method . '")');
            $this->fault->setEncoding($this->getEncoding());
            return false;
        }

        $this->method = $method;
        return true;
    }

    /**
     * Retrieve call method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Add a parameter to the parameter stack
     *
     * Adds a parameter to the parameter stack, associating it with the type
     * $type if provided
     *
     * @param mixed $value
     * @param string $type Optional; type hinting
     * @return void
     */
    public function addParam($value, $type = null)
    {
        $this->params[] = $value;
        if (null === $type) {
            // Detect type if not provided explicitly
            if ($value instanceof AbstractValue) {
                $type = $value->getType();
            } else {
                $xmlRpcValue = AbstractValue::getXmlRpcValue($value);
                $type        = $xmlRpcValue->getType();
            }
        }
        $this->types[]  = $type;
        $this->xmlRpcParams[] = array('value' => $value, 'type' => $type);
    }

    /**
     * Set the parameters array
     *
     * If called with a single, array value, that array is used to set the
     * parameters stack. If called with multiple values or a single non-array
     * value, the arguments are used to set the parameters stack.
     *
     * Best is to call with array of the format, in order to allow type hinting
     * when creating the XMLRPC values for each parameter:
     * <code>
     * $array = array(
     *     array(
     *         'value' => $value,
     *         'type'  => $type
     *     )[, ... ]
     * );
     * </code>
     *
     * @access public
     * @return void
     */
    public function setParams()
    {
        $argc = func_num_args();
        $argv = func_get_args();
        if (0 == $argc) {
            return;
        }

        if ((1 == $argc) && is_array($argv[0])) {
            $params     = array();
            $types      = array();
            $wellFormed = true;
            foreach ($argv[0] as $arg) {
                if (!is_array($arg) || !isset($arg['value'])) {
                    $wellFormed = false;
                    break;
                }
                $params[] = $arg['value'];

                if (!isset($arg['type'])) {
                    $xmlRpcValue = AbstractValue::getXmlRpcValue($arg['value']);
                    $arg['type'] = $xmlRpcValue->getType();
                }
                $types[] = $arg['type'];
            }
            if ($wellFormed) {
                $this->xmlRpcParams = $argv[0];
                $this->params = $params;
                $this->types  = $types;
            } else {
                $this->params = $argv[0];
                $this->types  = array();
                $xmlRpcParams  = array();
                foreach ($argv[0] as $arg) {
                    if ($arg instanceof AbstractValue) {
                        $type = $arg->getType();
                    } else {
                        $xmlRpcValue = AbstractValue::getXmlRpcValue($arg);
                        $type        = $xmlRpcValue->getType();
                    }
                    $xmlRpcParams[] = array('value' => $arg, 'type' => $type);
                    $this->types[] = $type;
                }
                $this->xmlRpcParams = $xmlRpcParams;
            }
            return;
        }

        $this->params = $argv;
        $this->types  = array();
        $xmlRpcParams  = array();
        foreach ($argv as $arg) {
            if ($arg instanceof AbstractValue) {
                $type = $arg->getType();
            } else {
                $xmlRpcValue = AbstractValue::getXmlRpcValue($arg);
                $type        = $xmlRpcValue->getType();
            }
            $xmlRpcParams[] = array('value' => $arg, 'type' => $type);
            $this->types[] = $type;
        }
        $this->xmlRpcParams = $xmlRpcParams;
    }

    /**
     * Retrieve the array of parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Return parameter types
     *
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Load XML and parse into request components
     *
     * @param string $request
     * @throws Exception\ValueException if invalid XML
     * @return bool True on success, false if an error occurred.
     */
    public function loadXml($request)
    {
        if (!is_string($request)) {
            $this->fault = new Fault(635);
            $this->fault->setEncoding($this->getEncoding());
            return false;
        }

        // @see ZF-12293 - disable external entities for security purposes
        $loadEntities  = libxml_disable_entity_loader(true);
        $xmlErrorsFlag = libxml_use_internal_errors(true);
        try {
            $dom = new DOMDocument;
            $dom->loadXML($request);
            foreach ($dom->childNodes as $child) {
                if ($child->nodeType === XML_DOCUMENT_TYPE_NODE) {
                    throw new Exception\ValueException(
                        'Invalid XML: Detected use of illegal DOCTYPE'
                    );
                }
            }
            ErrorHandler::start();
            $xml   = simplexml_import_dom($dom);
            $error = ErrorHandler::stop();
            libxml_disable_entity_loader($loadEntities);
            libxml_use_internal_errors($xmlErrorsFlag);
        } catch (\Exception $e) {
            // Not valid XML
            $this->fault = new Fault(631);
            $this->fault->setEncoding($this->getEncoding());
            libxml_disable_entity_loader($loadEntities);
            libxml_use_internal_errors($xmlErrorsFlag);
            return false;
        }
        if (!$xml instanceof SimpleXMLElement || $error) {
            // Not valid XML
            $this->fault = new Fault(631);
            $this->fault->setEncoding($this->getEncoding());
            libxml_use_internal_errors($xmlErrorsFlag);
            return false;
        }

        // Check for method name
        if (empty($xml->methodName)) {
            // Missing method name
            $this->fault = new Fault(632);
            $this->fault->setEncoding($this->getEncoding());
            return false;
        }

        $this->method = (string) $xml->methodName;

        // Check for parameters
        if (!empty($xml->params)) {
            $types = array();
            $argv  = array();
            foreach ($xml->params->children() as $param) {
                if (!isset($param->value)) {
                    $this->fault = new Fault(633);
                    $this->fault->setEncoding($this->getEncoding());
                    return false;
                }

                try {
                    $param   = AbstractValue::getXmlRpcValue($param->value, AbstractValue::XML_STRING);
                    $types[] = $param->getType();
                    $argv[]  = $param->getValue();
                } catch (\Exception $e) {
                    $this->fault = new Fault(636);
                    $this->fault->setEncoding($this->getEncoding());
                    return false;
                }
            }

            $this->types  = $types;
            $this->params = $argv;
        }

        $this->xml = $request;

        return true;
    }

    /**
     * Does the current request contain errors and should it return a fault
     * response?
     *
     * @return bool
     */
    public function isFault()
    {
        return $this->fault instanceof Fault;
    }

    /**
     * Retrieve the fault response, if any
     *
     * @return null|\Zend\XmlRpc\Fault
     */
    public function getFault()
    {
        return $this->fault;
    }

    /**
     * Retrieve method parameters as XMLRPC values
     *
     * @return array
     */
    protected function _getXmlRpcParams()
    {
        $params = array();
        if (is_array($this->xmlRpcParams)) {
            foreach ($this->xmlRpcParams as $param) {
                $value = $param['value'];
                $type  = $param['type'] ?: AbstractValue::AUTO_DETECT_TYPE;

                if (!$value instanceof AbstractValue) {
                    $value = AbstractValue::getXmlRpcValue($value, $type);
                }
                $params[] = $value;
            }
        }

        return $params;
    }

    /**
     * Create XML request
     *
     * @return string
     */
    public function saveXml()
    {
        $args   = $this->_getXmlRpcParams();
        $method = $this->getMethod();

        $generator = AbstractValue::getGenerator();
        $generator->openElement('methodCall')
                  ->openElement('methodName', $method)
                  ->closeElement('methodName');

        if (is_array($args) && count($args)) {
            $generator->openElement('params');

            foreach ($args as $arg) {
                $generator->openElement('param');
                $arg->generateXml();
                $generator->closeElement('param');
            }
            $generator->closeElement('params');
        }
        $generator->closeElement('methodCall');

        return $generator->flush();
    }

    /**
     * Return XML request
     *
     * @return string
     */
    public function __toString()
    {
        return $this->saveXML();
    }
}
