<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\XmlRpc\Client;

use Zend\XmlRpc\Client as XMLRPCClient;

/**
 * Wraps the XML-RPC system.* introspection methods
 */
class ServerIntrospection
{
    /**
     * @var \Zend\XmlRpc\Client\ServerProxy
     */
    private $system = null;


    /**
     * @param \Zend\XmlRpc\Client $client
     */
    public function __construct(XMLRPCClient $client)
    {
        $this->system = $client->getProxy('system');
    }

    /**
     * Returns the signature for each method on the server,
     * autodetecting whether system.multicall() is supported and
     * using it if so.
     *
     * @return array
     */
    public function getSignatureForEachMethod()
    {
        $methods = $this->listMethods();

        try {
            $signatures = $this->getSignatureForEachMethodByMulticall($methods);
        } catch (Exception\FaultException $e) {
            // degrade to looping
        }

        if (empty($signatures)) {
            $signatures = $this->getSignatureForEachMethodByLooping($methods);
        }

        return $signatures;
    }

    /**
     * Attempt to get the method signatures in one request via system.multicall().
     * This is a boxcar feature of XML-RPC and is found on fewer servers.  However,
     * can significantly improve performance if present.
     *
     * @param  array $methods
     * @throws Exception\IntrospectException
     * @return array array(array(return, param, param, param...))
     */
    public function getSignatureForEachMethodByMulticall($methods = null)
    {
        if ($methods === null) {
            $methods = $this->listMethods();
        }

        $multicallParams = array();
        foreach ($methods as $method) {
            $multicallParams[] = array('methodName' => 'system.methodSignature',
                                       'params'     => array($method));
        }

        $serverSignatures = $this->system->multicall($multicallParams);

        if (! is_array($serverSignatures)) {
            $type = gettype($serverSignatures);
            $error = "Multicall return is malformed.  Expected array, got $type";
            throw new Exception\IntrospectException($error);
        }

        if (count($serverSignatures) != count($methods)) {
            $error = 'Bad number of signatures received from multicall';
            throw new Exception\IntrospectException($error);
        }

        // Create a new signatures array with the methods name as keys and the signature as value
        $signatures = array();
        foreach ($serverSignatures as $i => $signature) {
            $signatures[$methods[$i]] = $signature;
        }

        return $signatures;
    }

    /**
     * Get the method signatures for every method by
     * successively calling system.methodSignature
     *
     * @param array $methods
     * @return array
     */
    public function getSignatureForEachMethodByLooping($methods = null)
    {
        if ($methods === null) {
            $methods = $this->listMethods();
        }

        $signatures = array();
        foreach ($methods as $method) {
            $signatures[$method] = $this->getMethodSignature($method);
        }

        return $signatures;
    }

    /**
     * Call system.methodSignature() for the given method
     *
     * @param  array  $method
     * @throws Exception\IntrospectException
     * @return array  array(array(return, param, param, param...))
     */
    public function getMethodSignature($method)
    {
        $signature = $this->system->methodSignature($method);
        if (!is_array($signature)) {
            $error = 'Invalid signature for method "' . $method . '"';
            throw new Exception\IntrospectException($error);
        }
        return $signature;
    }

    /**
     * Call system.listMethods()
     *
     * @return array  array(method, method, method...)
     */
    public function listMethods()
    {
        return $this->system->listMethods();
    }
}
