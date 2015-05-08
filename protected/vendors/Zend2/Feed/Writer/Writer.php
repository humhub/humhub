<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Writer;

/**
*/
class Writer
{
    /**
     * Namespace constants
     */
    const NAMESPACE_ATOM_03  = 'http://purl.org/atom/ns#';
    const NAMESPACE_ATOM_10  = 'http://www.w3.org/2005/Atom';
    const NAMESPACE_RDF      = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
    const NAMESPACE_RSS_090  = 'http://my.netscape.com/rdf/simple/0.9/';
    const NAMESPACE_RSS_10   = 'http://purl.org/rss/1.0/';

    /**
     * Feed type constants
     */
    const TYPE_ANY              = 'any';
    const TYPE_ATOM_03          = 'atom-03';
    const TYPE_ATOM_10          = 'atom-10';
    const TYPE_ATOM_ANY         = 'atom';
    const TYPE_RSS_090          = 'rss-090';
    const TYPE_RSS_091          = 'rss-091';
    const TYPE_RSS_091_NETSCAPE = 'rss-091n';
    const TYPE_RSS_091_USERLAND = 'rss-091u';
    const TYPE_RSS_092          = 'rss-092';
    const TYPE_RSS_093          = 'rss-093';
    const TYPE_RSS_094          = 'rss-094';
    const TYPE_RSS_10           = 'rss-10';
    const TYPE_RSS_20           = 'rss-20';
    const TYPE_RSS_ANY          = 'rss';

    /**
     * @var ExtensionManagerInterface
     */
    protected static $extensionManager = null;

    /**
     * Array of registered extensions by class postfix (after the base class
     * name) across four categories - data containers and renderers for entry
     * and feed levels.
     *
     * @var array
     */
    protected static $extensions = array(
        'entry'         => array(),
        'feed'          => array(),
        'entryRenderer' => array(),
        'feedRenderer'  => array(),
    );

    /**
     * Set plugin loader for use with Extensions
     *
     * @param ExtensionManagerInterface
     */
    public static function setExtensionManager(ExtensionManagerInterface $extensionManager)
    {
        static::$extensionManager = $extensionManager;
    }

    /**
     * Get plugin manager for use with Extensions
     *
     * @return ExtensionManagerInterface
     */
    public static function getExtensionManager()
    {
        if (!isset(static::$extensionManager)) {
            static::setExtensionManager(new ExtensionManager());
        }
        return static::$extensionManager;
    }

    /**
     * Register an Extension by name
     *
     * @param  string $name
     * @return void
     * @throws Exception\RuntimeException if unable to resolve Extension class
     */
    public static function registerExtension($name)
    {
        $feedName          = $name . '\Feed';
        $entryName         = $name . '\Entry';
        $feedRendererName  = $name . '\Renderer\Feed';
        $entryRendererName = $name . '\Renderer\Entry';
        $manager           = static::getExtensionManager();
        if (static::isRegistered($name)) {
            if ($manager->has($feedName)
                || $manager->has($entryName)
                || $manager->has($feedRendererName)
                || $manager->has($entryRendererName)
            ) {
                return;
            }
        }
        if (!$manager->has($feedName)
            && !$manager->has($entryName)
            && !$manager->has($feedRendererName)
            && !$manager->has($entryRendererName)
        ) {
            throw new Exception\RuntimeException('Could not load extension: ' . $name
                . 'using Plugin Loader. Check prefix paths are configured and extension exists.');
        }
        if ($manager->has($feedName)) {
            static::$extensions['feed'][] = $feedName;
        }
        if ($manager->has($entryName)) {
            static::$extensions['entry'][] = $entryName;
        }
        if ($manager->has($feedRendererName)) {
            static::$extensions['feedRenderer'][] = $feedRendererName;
        }
        if ($manager->has($entryRendererName)) {
            static::$extensions['entryRenderer'][] = $entryRendererName;
        }
    }

    /**
     * Is a given named Extension registered?
     *
     * @param  string $extensionName
     * @return bool
     */
    public static function isRegistered($extensionName)
    {
        $feedName          = $extensionName . '\Feed';
        $entryName         = $extensionName . '\Entry';
        $feedRendererName  = $extensionName . '\Renderer\Feed';
        $entryRendererName = $extensionName . '\Renderer\Entry';
        if (in_array($feedName, static::$extensions['feed'])
            || in_array($entryName, static::$extensions['entry'])
            || in_array($feedRendererName, static::$extensions['feedRenderer'])
            || in_array($entryRendererName, static::$extensions['entryRenderer'])
        ) {
            return true;
        }
        return false;
    }

    /**
     * Get a list of extensions
     *
     * @return array
     */
    public static function getExtensions()
    {
        return static::$extensions;
    }

    /**
     * Reset class state to defaults
     *
     * @return void
     */
    public static function reset()
    {
        static::$extensionManager = null;
        static::$extensions   = array(
            'entry'         => array(),
            'feed'          => array(),
            'entryRenderer' => array(),
            'feedRenderer'  => array(),
        );
    }

    /**
     * Register core (default) extensions
     *
     * @return void
     */
    public static function registerCoreExtensions()
    {
        static::registerExtension('DublinCore');
        static::registerExtension('Content');
        static::registerExtension('Atom');
        static::registerExtension('Slash');
        static::registerExtension('WellFormedWeb');
        static::registerExtension('Threading');
        static::registerExtension('ITunes');
    }

    public static function lcfirst($str)
    {
        $str[0] = strtolower($str[0]);
        return $str;
    }
}
