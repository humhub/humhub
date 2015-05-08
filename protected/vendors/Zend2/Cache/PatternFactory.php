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

abstract class PatternFactory
{
    /**
     * The pattern manager
     *
     * @var null|PatternPluginManager
     */
    protected static $plugins = null;

    /**
     * Instantiate a cache pattern
     *
     * @param  string|Pattern\PatternInterface $patternName
     * @param  array|Traversable|Pattern\PatternOptions $options
     * @return Pattern\PatternInterface
     * @throws Exception\InvalidArgumentException
     */
    public static function factory($patternName, $options = array())
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }
        if (is_array($options)) {
            $options = new Pattern\PatternOptions($options);
        } elseif (!$options instanceof Pattern\PatternOptions) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array, Traversable object, or %s\Pattern\PatternOptions object; received "%s"',
                __METHOD__,
                __NAMESPACE__,
                (is_object($options) ? get_class($options) : gettype($options))
            ));
        }

        if ($patternName instanceof Pattern\PatternInterface) {
            $patternName->setOptions($options);
            return $patternName;
        }

        $pattern = static::getPluginManager()->get($patternName);
        $pattern->setOptions($options);
        return $pattern;
    }

    /**
     * Get the pattern plugin manager
     *
     * @return PatternPluginManager
     */
    public static function getPluginManager()
    {
        if (static::$plugins === null) {
            static::$plugins = new PatternPluginManager();
        }

        return static::$plugins;
    }

    /**
     * Set the pattern plugin manager
     *
     * @param  PatternPluginManager $plugins
     * @return void
     */
    public static function setPluginManager(PatternPluginManager $plugins)
    {
        static::$plugins = $plugins;
    }

    /**
     * Reset pattern plugin manager to default
     *
     * @return void
     */
    public static function resetPluginManager()
    {
        static::$plugins = null;
    }
}
