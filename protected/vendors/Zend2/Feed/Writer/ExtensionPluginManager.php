<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Writer;

use Zend\ServiceManager\AbstractPluginManager;

/**
 * Plugin manager implementation for feed writer extensions
 *
 * Validation checks that we have an Entry, Feed, or Extension\AbstractRenderer.
 */
class ExtensionPluginManager extends AbstractPluginManager
{
    /**
     * Default set of extension classes
     *
     * @var array
     */
    protected $invokableClasses = array(
        'atomrendererfeed'           => 'Zend\Feed\Writer\Extension\Atom\Renderer\Feed',
        'contentrendererentry'       => 'Zend\Feed\Writer\Extension\Content\Renderer\Entry',
        'dublincorerendererentry'    => 'Zend\Feed\Writer\Extension\DublinCore\Renderer\Entry',
        'dublincorerendererfeed'     => 'Zend\Feed\Writer\Extension\DublinCore\Renderer\Feed',
        'itunesentry'                => 'Zend\Feed\Writer\Extension\ITunes\Entry',
        'itunesfeed'                 => 'Zend\Feed\Writer\Extension\ITunes\Feed',
        'itunesrendererentry'        => 'Zend\Feed\Writer\Extension\ITunes\Renderer\Entry',
        'itunesrendererfeed'         => 'Zend\Feed\Writer\Extension\ITunes\Renderer\Feed',
        'slashrendererentry'         => 'Zend\Feed\Writer\Extension\Slash\Renderer\Entry',
        'threadingrendererentry'     => 'Zend\Feed\Writer\Extension\Threading\Renderer\Entry',
        'wellformedwebrendererentry' => 'Zend\Feed\Writer\Extension\WellFormedWeb\Renderer\Entry',
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
        if ($plugin instanceof Extension\AbstractRenderer) {
            // we're okay
            return;
        }

        if ('Feed' == substr(get_class($plugin), -4)) {
            // we're okay
            return;
        }

        if ('Entry' == substr(get_class($plugin), -5)) {
            // we're okay
            return;
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Extension\RendererInterface '
            . 'or the classname must end in "Feed" or "Entry"',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
