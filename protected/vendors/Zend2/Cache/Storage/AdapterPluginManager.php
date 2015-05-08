<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage;

use Zend\Cache\Exception;
use Zend\ServiceManager\AbstractPluginManager;

/**
 * Plugin manager implementation for cache storage adapters
 *
 * Enforces that adapters retrieved are instances of
 * StorageInterface. Additionally, it registers a number of default
 * adapters available.
 */
class AdapterPluginManager extends AbstractPluginManager
{
    /**
     * Default set of adapters
     *
     * @var array
     */
    protected $invokableClasses = array(
        'apc'            => 'Zend\Cache\Storage\Adapter\Apc',
        'dba'            => 'Zend\Cache\Storage\Adapter\Dba',
        'filesystem'     => 'Zend\Cache\Storage\Adapter\Filesystem',
        'memcached'      => 'Zend\Cache\Storage\Adapter\Memcached',
        'memory'         => 'Zend\Cache\Storage\Adapter\Memory',
        'redis'          => 'Zend\Cache\Storage\Adapter\Redis',
        'session'        => 'Zend\Cache\Storage\Adapter\Session',
        'xcache'         => 'Zend\Cache\Storage\Adapter\XCache',
        'wincache'       => 'Zend\Cache\Storage\Adapter\WinCache',
        'zendserverdisk' => 'Zend\Cache\Storage\Adapter\ZendServerDisk',
        'zendservershm'  => 'Zend\Cache\Storage\Adapter\ZendServerShm',
    );

    /**
     * Do not share by default
     *
     * @var array
     */
    protected $shareByDefault = false;

    /**
     * Validate the plugin
     *
     * Checks that the adapter loaded is an instance of StorageInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\RuntimeException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof StorageInterface) {
            // we're okay
            return;
        }

        throw new Exception\RuntimeException(sprintf(
            'Plugin of type %s is invalid; must implement %s\StorageInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
