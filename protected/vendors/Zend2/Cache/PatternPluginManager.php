<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache;

use Zend\ServiceManager\AbstractPluginManager;

/**
 * Plugin manager implementation for cache pattern adapters
 *
 * Enforces that adatpers retrieved are instances of
 * Pattern\PatternInterface. Additionally, it registers a number of default
 * patterns available.
 */
class PatternPluginManager extends AbstractPluginManager
{
    /**
     * Default set of adapters
     *
     * @var array
     */
    protected $invokableClasses = array(
        'callback' => 'Zend\Cache\Pattern\CallbackCache',
        'capture'  => 'Zend\Cache\Pattern\CaptureCache',
        'class'    => 'Zend\Cache\Pattern\ClassCache',
        'object'   => 'Zend\Cache\Pattern\ObjectCache',
        'output'   => 'Zend\Cache\Pattern\OutputCache',
        'page'     => 'Zend\Cache\Pattern\PageCache',
    );

    /**
     * Don't share by default
     *
     * @var array
     */
    protected $shareByDefault = false;

    /**
     * Validate the plugin
     *
     * Checks that the pattern adapter loaded is an instance of Pattern\PatternInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\RuntimeException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof Pattern\PatternInterface) {
            // we're okay
            return;
        }

        throw new Exception\RuntimeException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Pattern\PatternInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
