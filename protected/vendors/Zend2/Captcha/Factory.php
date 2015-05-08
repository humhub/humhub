<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Captcha;

use Traversable;
use Zend\Stdlib\ArrayUtils;

abstract class Factory
{
    /**
     * @var array Known captcha types
     */
    protected static $classMap = array(
        'dumb'      => 'Zend\Captcha\Dumb',
        'figlet'    => 'Zend\Captcha\Figlet',
        'image'     => 'Zend\Captcha\Image',
        'recaptcha' => 'Zend\Captcha\ReCaptcha',
    );

    /**
     * Create a captcha adapter instance
     *
     * @param  array|Traversable $options
     * @return AdapterInterface
     * @throws Exception\InvalidArgumentException for a non-array, non-Traversable $options
     * @throws Exception\DomainException if class is missing or invalid
     */
    public static function factory($options)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (!is_array($options)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array or Traversable argument; received "%s"',
                __METHOD__,
                (is_object($options) ? get_class($options) : gettype($options))
            ));
        }

        if (!isset($options['class'])) {
            throw new Exception\DomainException(sprintf(
                '%s expects a "class" attribute in the options; none provided',
                __METHOD__
            ));
        }

        $class = $options['class'];
        if (isset(static::$classMap[strtolower($class)])) {
            $class = static::$classMap[strtolower($class)];
        }
        if (!class_exists($class)) {
            throw new Exception\DomainException(sprintf(
                '%s expects the "class" attribute to resolve to an existing class; received "%s"',
                __METHOD__,
                $class
            ));
        }

        unset($options['class']);

        if (isset($options['options'])) {
            $options = $options['options'];
        }
        $captcha = new $class($options);

        if (!$captcha instanceof AdapterInterface) {
            throw new Exception\DomainException(sprintf(
                '%s expects the "class" attribute to resolve to a valid Zend\Captcha\AdapterInterface instance; received "%s"',
                __METHOD__,
                $class
            ));
        }

        return $captcha;
    }
}
