<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\XmlRpc;

/**
 * XmlRpc Response
 *
 * Container for accessing an XMLRPC return value and creating the XML response.
 */
class Response
{
    /**
     * Return value
     * @var mixed
     */
    protected $return;

    /**
     * Return type
     * @var string
     */
    protected $type;

    /**
     * Response character encoding
     * @var string
     */
    protected $encoding = 'UTF-8';

    /**
     * Fault, if response is a fault response
     * @var null|\Zend\XmlRpc\Fault
     */
    protected $fault = null;

    /**
     * Constructor
     *
     * Can optionally pass in the return value and type hinting; otherwise, the
     * return value can be set via {@link setReturnValue()}.
     *
     * @param mixed $return
     * @param string $type
     */
    public function __construct($return = null, $type = null)
    {
        $this->setReturnValue($return, $type);
    }

    /**
     * Set encoding to use in response
     *
     * @param string $encoding
     * @return \Zend\XmlRpc\Response
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
        AbstractValue::setEncoding($encoding);
        return $this;
    }

    /**
     * Retrieve current response encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Set the return value
     *
     * Sets the return value, with optional type hinting if provided.
     *
     * @param mixed $value
     * @param string $type
     * @return void
     */
    public function setReturnValue($value, $type = null)
    {
        $this->return = $value;
        $this->type = (string) $type;
    }

    /**
     * Retrieve the return value
     *
     * @return mixed
     */
    public function getReturnValue()
    {
        return $this->return;
    }

    /**
     * Retrieve the XMLRPC value for the return value
     *
     * @return \Zend\XmlRpc\AbstractValue
     */
    protected function _getXmlRpcReturn()
    {
        return AbstractValue::getXmlRpcValue($this->return);
    }

    /**
     * Is the response a fault response?
     *
     * @return bool
     */
    public function isFault()
    {
        return $this->fault instanceof Fault;
    }

    /**
     * Returns the fault, if any.
     *
     * @return null|\Zend\XmlRpc\Fault
     */
    public function getFault()
    {
        return $this->fault;
    }

    /**
     * Load a response from an XML response
     *
     * Attempts to load a response from an XMLRPC response, autodetecting if it
     * is a fault response.
     *
     * @param string $response
     * @throws Exception\ValueException if invalid XML
     * @return bool True if a valid XMLRPC response, false if a fault
     * response or invalid input
     */
    public function loadXml($response)
    {
        if (!is_string($response)) {
            $this->fault = new Fault(650);
            $this->fault->setEncoding($this->getEncoding());
            return false;
        }

        // @see ZF-12293 - disable external entities for security purposes
        $loadEntities         = libxml_disable_entity_loader(true);
        $useInternalXmlErrors = libxml_use_internal_errors(true);
        try {
            $dom = new \DOMDocument;
            $dom->loadXML($response);
            foreach ($dom->childNodes as $child) {
                if ($child->nodeType === XML_DOCUMENT_TYPE_NODE) {
                    throw new Exception\ValueException(
                        'Invalid XML: Detected use of illegal DOCTYPE'
                    );
                }
            }
            // TODO: Locate why this passes tests but a simplexml import doesn't
            //$xml = simplexml_import_dom($dom);
            $xml = new \SimpleXMLElement($response);
            libxml_disable_entity_loader($loadEntities);
            libxml_use_internal_errors($useInternalXmlErrors);
        } catch (\Exception $e) {
            libxml_disable_entity_loader($loadEntities);
            libxml_use_internal_errors($useInternalXmlErrors);
            // Not valid XML
            $this->fault = new Fault(651);
            $this->fault->setEncoding($this->getEncoding());
            return false;
        }

        if (!empty($xml->fault)) {
            // fault response
            $this->fault = new Fault();
            $this->fault->setEncoding($this->getEncoding());
            $this->fault->loadXml($response);
            return false;
        }

        if (empty($xml->params)) {
            // Invalid response
            $this->fault = new Fault(652);
            $this->fault->setEncoding($this->getEncoding());
            return false;
        }

        try {
            if (!isset($xml->params) || !isset($xml->params->param) || !isset($xml->params->param->value)) {
                throw new Exception\ValueException('Missing XML-RPC value in XML');
            }
            $valueXml = $xml->params->param->value->asXML();
            $value = AbstractValue::getXmlRpcValue($valueXml, AbstractValue::XML_STRING);
        } catch (Exception\ValueException $e) {
            $this->fault = new Fault(653);
            $this->fault->setEncoding($this->getEncoding());
            return false;
        }

        $this->setReturnValue($value->getValue());
        return true;
    }

    /**
     * Return response as XML
     *
     * @return string
     */
    public function saveXml()
    {
        $value = $this->_getXmlRpcReturn();
        $generator = AbstractValue::getGenerator();
        $generator->openElement('methodResponse')
                  ->openElement('params')
                  ->openElement('param');
        $value->generateXml();
        $generator->closeElement('param')
                  ->closeElement('params')
                  ->closeElement('methodResponse');

        return $generator->flush();
    }

    /**
     * Return XML response
     *
     * @return string
     */
    public function __toString()
    {
        return $this->saveXML();
    }
}
