<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Tag\Cloud;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\Tag\Exception;

/**
 * Plugin manager implementation for decorators.
 *
 * Enforces that decorators retrieved are instances of
 * Decorator\DecoratorInterface. Additionally, it registers a number of default
 * decorators available.
 */
class DecoratorPluginManager extends AbstractPluginManager
{
    /**
     * Default set of decorators
     *
     * @var array
     */
    protected $invokableClasses = array(
        'htmlcloud' => 'Zend\Tag\Cloud\Decorator\HtmlCloud',
        'htmltag'   => 'Zend\Tag\Cloud\Decorator\HtmlTag',
        'tag'       => 'Zend\Tag\Cloud\Decorator\HtmlTag',
   );

    /**
     * Validate the plugin
     *
     * Checks that the decorator loaded is an instance
     * of Decorator\DecoratorInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\InvalidArgumentException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof Decorator\DecoratorInterface) {
            // we're okay
            return;
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Decorator\DecoratorInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
