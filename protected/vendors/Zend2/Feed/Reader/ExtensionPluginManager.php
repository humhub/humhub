<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Reader;

use Zend\ServiceManager\AbstractPluginManager;

/**
 * Plugin manager implementation for feed reader extensions based on the
 * AbstractPluginManager.
 *
 * Validation checks that we have an Extension\AbstractEntry or
 * Extension\AbstractFeed.
 */
class ExtensionPluginManager extends AbstractPluginManager
{
    /**
     * Default set of extension classes
     *
     * @var array
     */
    protected $invokableClasses = array(
        'atomentry'            => 'Zend\Feed\Reader\Extension\Atom\Entry',
        'atomfeed'             => 'Zend\Feed\Reader\Extension\Atom\Feed',
        'contententry'         => 'Zend\Feed\Reader\Extension\Content\Entry',
        'creativecommonsentry' => 'Zend\Feed\Reader\Extension\CreativeCommons\Entry',
        'creativecommonsfeed'  => 'Zend\Feed\Reader\Extension\CreativeCommons\Feed',
        'dublincoreentry'      => 'Zend\Feed\Reader\Extension\DublinCore\Entry',
        'dublincorefeed'       => 'Zend\Feed\Reader\Extension\DublinCore\Feed',
        'podcastentry'         => 'Zend\Feed\Reader\Extension\Podcast\Entry',
        'podcastfeed'          => 'Zend\Feed\Reader\Extension\Podcast\Feed',
        'slashentry'           => 'Zend\Feed\Reader\Extension\Slash\Entry',
        'syndicationfeed'      => 'Zend\Feed\Reader\Extension\Syndication\Feed',
        'threadentry'          => 'Zend\Feed\Reader\Extension\Thread\Entry',
        'wellformedwebentry'   => 'Zend\Feed\Reader\Extension\WellFormedWeb\Entry',
    );

    /**
     * Do not share instances
     *
     * @var bool
     */
    protected $shareByDefault = false;

    /**
     * Validate the plugin
     *
     * Checks that the extension loaded is of a valid type.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\InvalidArgumentException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof Extension\AbstractEntry
            || $plugin instanceof Extension\AbstractFeed
        ) {
            // we're okay
            return;
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Extension\AbstractFeed '
            . 'or %s\Extension\AbstractEntry',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__,
            __NAMESPACE__
        ));
    }
}
