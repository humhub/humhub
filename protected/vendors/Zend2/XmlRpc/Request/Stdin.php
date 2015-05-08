<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\XmlRpc\Request;

use Zend\XmlRpc\Request as XmlRpcRequest;
use Zend\XmlRpc\Fault;

/**
 * XmlRpc Request object -- Request via STDIN
 *
 * Extends {@link Zend\XmlRpc\Request} to accept a request via STDIN. Request is
 * built at construction time using data from STDIN; if no data is available, the
 * request is declared a fault.
 */
class Stdin extends XmlRpcRequest
{
    /**
     * Raw XML as received via request
     * @var string
     */
    protected $xml;

    /**
     * Constructor
     *
     * Attempts to read from php://stdin to get raw POST request; if an error
     * occurs in doing so, or if the XML is invalid, the request is declared a
     * fault.
     *
     */
    public function __construct()
    {
        $fh = fopen('php://stdin', 'r');
        if (!$fh) {
            $this->fault = new Fault(630);
            return;
        }

        $xml = '';
        while (!feof($fh)) {
            $xml .= fgets($fh);
        }
        fclose($fh);

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
}
