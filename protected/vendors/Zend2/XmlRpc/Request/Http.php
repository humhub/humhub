<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\XmlRpc\Request;

use Zend\Stdlib\ErrorHandler;
use Zend\XmlRpc\Fault;
use Zend\XmlRpc\Request as XmlRpcRequest;

/**
 * XmlRpc Request object -- Request via HTTP
 *
 * Extends {@link Zend\XmlRpc\Request} to accept a request via HTTP. Request is
 * built at construction time using a raw POST; if no data is available, the
 * request is declared a fault.
 */
class Http extends XmlRpcRequest
{
    /**
     * Array of headers
     * @var array
     */
    protected $headers;

    /**
     * Raw XML as received via request
     * @var string
     */
    protected $xml;

    /**
     * Constructor
     *
     * Attempts to read from php://input to get raw POST request; if an error
     * occurs in doing so, or if the XML is invalid, the request is declared a
     * fault.
     *
     */
    public function __construct()
    {
        ErrorHandler::start();
        $xml = file_get_contents('php://input');
        ErrorHandler::stop();
        if (!$xml) {
            $this->fault = new Fault(630);
            return;
        }

        $this->xml = $xml;

        $this->loadXml($xml);
    }

    /**
     * Retrieve the raw XML request
     *
     * @return string
     */
    public function getRawRequest()
    {
        return $this->xml;
    }

    /**
     * Get headers
     *
     * Gets all headers as key => value pairs and returns them.
     *
     * @return array
     */
    public function getHeaders()
    {
        if (null === $this->headers) {
            $this->headers = array();
            foreach ($_SERVER as $key => $value) {
                if ('HTTP_' == substr($key, 0, 5)) {
                    $header = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                    $this->headers[$header] = $value;
                }
            }
        }

        return $this->headers;
    }

    /**
     * Retrieve the full HTTP request, including headers and XML
     *
     * @return string
     */
    public function getFullRequest()
    {
        $request = '';
        foreach ($this->getHeaders() as $key => $value) {
            $request .= $key . ': ' . $value . "\n";
        }

        $request .= $this->xml;

        return $request;
    }
}
