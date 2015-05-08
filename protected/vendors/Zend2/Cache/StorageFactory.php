<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache;

use Traversable;
use Zend\Stdlib\ArrayUtils;

abstract class StorageFactory
{
    /**
     * Plugin manager for loading adapters
     *
     * @var null|Storage\AdapterPluginManager
     */
    protected static $adapters = null;

    /**
     * Plugin manager for loading plugins
     *
     * @var null|Storage\PluginManager
     */
    protected static $plugins = null;

    /**
     * The storage factory
     * This can instantiate storage adapters and plugins.
     *
     * @param array|Traversable $cfg
     * @return Storage\StorageInterface
     * @throws Exception\InvalidArgumentException
     */
    public static function factory($cfg)
    {
        if ($cfg instanceof Traversable) {
            $cfg = ArrayUtils::iteratorToArray($cfg);
        }

        if (!is_array($cfg)) {
            throw new Exception\InvalidArgumentException(
                'The factory needs an associative array '
                . 'or a Traversable object as an argument'
            );
        }

        // instantiate the adapter
        if (!isset($cfg['adapter'])) {
            throw new Exception\InvalidArgumentException('Missing "adapter"');
        }
        $adapterName    = $cfg['adapter'];
        $adapterOptions = array();
        if (is_array($cfg['adapter'])) {
            if (!isset($cfg['adapter']['name'])) {
                throw new Exception\InvalidArgumentException('Missing "adapter.name"');
            }

            $adapterName    = $cfg['adapter']['name'];
            $adapterOptions = isset($cfg['adapter']['options']) ? $cfg['adapter']['options'] : array();
        }
        if (isset($cfg['options'])) {
            $adapterOptions = array_merge($adapterOptions, $cfg['options']);
        }

        $adapter = static::adapterFactory((string) $adapterName, $adapterOptions);

        // add plugins
        if (isset($cfg['plugins'])) {
            if (!is_array($cfg['plugins'])) {
                throw new Exception\InvalidArgumentException(
                    'Plugins needs to be an array'
                );
            }

            foreach ($cfg['plugins'] as $k => $v) {
                $pluginPrio = 1; // default priority

                if (is_string($k)) {
                    if (!is_array($v)) {
                        throw new Exception\InvalidArgumentException(
                            "'plugins.{$k}' needs to be an array"
                        );
                    }
                    $pluginName    = $k;
                    $pluginOptions = $v;
                } elseif (is_array($v)) {
                    if (!isset($v['name'])) {
                        throw new Exception\InvalidArgumentException("Invalid plugins[{$k}] or missing plugins[{$k}].name");
                    }
                    $pluginName = (string) $v['name'];

                    if (isset($v['options'])) {
                        $pluginOptions = $v['options'];
                    } else {
                        $pluginOptions = array();
                    }

                    if (isset($v['priority'])) {
                        $pluginPrio = $v['priority'];
                    }
                } else {
                    $pluginName    = $v;
                    $pluginOptions = array();
                }

                $plugin = static::pluginFactory($pluginName, $pluginOptions);
                $adapter->addPlugin($plugin, $pluginPrio);
            }
        }

        return $adapter;
    }

    /**
     * Instantiate a storage adapter
     *
     * @param  string|Storage\StorageInterface                  $adapterName
     * @param  array|Traversable|Storage\Adapter\AdapterOptions $options
     * @return Storage\StorageInterface
     * @throws Exception\RuntimeException
     */
    public static function adapterFactory($adapterName, $options = array())
    {
        if ($adapterName instanceof Storage\StorageInterface) {
            // $adapterName is already an adapter object
            $adapter = $adapterName;
        } else {
            $adapter = static::getAdapterPluginManager()->get($adapterName);
        }

        if ($options) {
            $adapter->setOptions($options);
        }

        return $adapter;
    }

    /**
     * Get the adapter plugin manager
     *
     * @return Storage\AdapterPluginManager
     */
    public static function getAdapterPluginManager()
    {
        if (static::$adapters === null) {
            static::$adapters = new Storage\AdapterPluginManager();
        }
        return static::$adapters;
    }

    /**
     * Change the adapter plugin manager
     *
     * @param  Storage\AdapterPluginManager $adapters
     * @return void
     */
    public static function setAdapterPluginManager(Storage\AdapterPluginManager $adapters)
    {
        static::$adapters = $adapters;
    }

    /**
     * Resets the internal adapter plugin manager
     *
     * @return void
     */
    public static function resetAdapterPluginManager()
    {
        static::$adapters = null;
    }

    /**
     * Instantiate a storage plugin
     *
     * @param string|Storage\Plugin\PluginInterface     $pluginName
     * @param array|Traversable|Storage\Plugin\PluginOptions $options
     * @return Storage\Plugin\PluginInterface
     * @throws Exception\RuntimeException
     */
    public static function pluginFactory($pluginName, $options = array())
    {
        if ($pluginName instanceof Storage\Plugin\PluginInterface) {
            // $pluginName is already an plugin object
            $plugin = $pluginName;
        } else {
            $plugin = static::getPluginManager()->get($pluginName);
        }

        if (!$options instanceof Storage\Plugin\PluginOptions) {
            $options = new Storage\Plugin\PluginOptions($options);
        }

        if ($options) {
            $plugin->setOptions($options);
        }

        return $plugin;
    }

    /**
     * Get the plugin manager
     *
     * @return Storage\PluginManager
     */
    public static function getPluginManager()
    {
        if (static::$plugins === null) {
            static::$plugins = new Storage\PluginManager();
        }
        return static::$plugins;
    }

    /**
     * Change the plugin manager
     *
     * @param  Storage\PluginManager $plugins
     * @return void
     */
    public static function setPluginManager(Storage\PluginManager $plugins)
    {
        static::$plugins = $plugins;
    }

    /**
     * Resets the internal plugin manager
     *
     * @return void
     */
    public static function resetPluginManager()
    {
        static::$plugins = null;
    }
}
