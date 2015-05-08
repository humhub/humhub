<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\XmlRpc\Server;

/**
 * Zend\XmlRpc\Server\Cache: cache Zend\XmlRpc\Server server definition
 */
class Cache extends \Zend\Server\Cache
{
    /**
     * @var array Skip system methods when caching XML-RPC server
     */
    protected static $skipMethods = array(
        'system.listMethods',
        'system.methodHelp',
        'system.methodSignature',
        'system.multicall',
    );
}
