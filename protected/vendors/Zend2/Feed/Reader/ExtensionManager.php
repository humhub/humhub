<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Reader;

/**
 * Default implementation of ExtensionManagerInterface
 *
 * Decorator of ExtensionPluginManager.
 */
class ExtensionManager implements ExtensionManagerInterface
{
    protected $pluginManager;

    /**
     * Constructor
     *
     * Seeds the extension manager with a plugin manager; if none provided,
     * creates an instance.
     *
     * @param  null|ExtensionPluginManager $pluginManager
     */
    public function __construct(ExtensionPluginManager $pluginManager = null)
    {
        if (null === $pluginManager) {
            $pluginManager = new ExtensionPluginManager();
        }
        $this->pluginManager = $pluginManager;
    }

    /**
     * Method overloading
     *
     * Proxy to composed ExtensionPluginManager instance.
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     * @throws Exception\BadMethodCallException
     */
    public function __call($method, $args)
    {
        if (!method_exists($this->pluginManager, $method)) {
            throw new Exception\BadMethodCallException(sprintf(
                'Method by name of %s does not exist in %s',
                $method,
                __CLASS__
            ));
        }
        return call_user_func_array(array($this->pluginManager, $method), $args);
    }

    /**
     * Get the named extension
     *
     * @param  string $name
     * @return Extension\AbstractEntry|Extension\AbstractFeed
     */
    public function get($name)
    {
        return $this->pluginManager->get($name);
    }

    /**
     * Do we have the named extension?
     *
     * @param  string $name
     * @return bool
     */
    public function has($name)
    {
        return $this->pluginManager->has($name);
    }
}
