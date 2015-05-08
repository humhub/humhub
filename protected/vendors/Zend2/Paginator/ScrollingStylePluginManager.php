<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Paginator;

use Zend\ServiceManager\AbstractPluginManager;

/**
 * Plugin manager implementation for scrolling style adapters
 *
 * Enforces that adapters retrieved are instances of
 * ScrollingStyle\ScrollingStyleInterface. Additionally, it registers a number
 * of default adapters available.
 */
class ScrollingStylePluginManager extends AbstractPluginManager
{
    /**
     * Default set of adapters
     *
     * @var array
     */
    protected $invokableClasses = array(
        'all'     => 'Zend\Paginator\ScrollingStyle\All',
        'elastic' => 'Zend\Paginator\ScrollingStyle\Elastic',
        'jumping' => 'Zend\Paginator\ScrollingStyle\Jumping',
        'sliding' => 'Zend\Paginator\ScrollingStyle\Sliding',
    );

    /**
     * Validate the plugin
     *
     * Checks that the adapter loaded is an instance of ScrollingStyle\ScrollingStyleInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\InvalidArgumentException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof ScrollingStyle\ScrollingStyleInterface) {
            // we're okay
            return;
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'Plugin of type %s is invalid; must implement %s\ScrollingStyle\ScrollingStyleInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
