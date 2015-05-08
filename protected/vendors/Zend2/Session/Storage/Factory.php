<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Session\Storage;

use ArrayAccess;
use Traversable;
use Zend\Session\Exception;
use Zend\Stdlib\ArrayObject;
use Zend\Stdlib\ArrayUtils;

abstract class Factory
{
    /**
     * Create and return a StorageInterface instance
     *
     * @param  string                             $type
     * @param  array|Traversable                  $options
     * @return StorageInterface
     * @throws Exception\InvalidArgumentException for unrecognized $type or individual options
     */
    public static function factory($type, $options = array())
    {
        if (!is_string($type)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects the $type argument to be a string class name; received "%s"',
                __METHOD__,
                (is_object($type) ? get_class($type) : gettype($type))
            ));
        }
        if (!class_exists($type)) {
            $class = __NAMESPACE__ . '\\' . $type;
            if (!class_exists($class)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    '%s expects the $type argument to be a valid class name; received "%s"',
                    __METHOD__,
                    $type
                ));
            }
            $type = $class;
        }

        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }
        if (!is_array($options)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects the $options argument to be an array or Traversable; received "%s"',
                __METHOD__,
                (is_object($options) ? get_class($options) : gettype($options))
            ));
        }

        switch (true) {
            case (in_array('Zend\Session\Storage\AbstractSessionArrayStorage', class_parents($type))):
                return static::createSessionArrayStorage($type, $options);
                break;
            case ($type === 'Zend\Session\Storage\ArrayStorage'):
            case (in_array('Zend\Session\Storage\ArrayStorage', class_parents($type))):
                return static::createArrayStorage($type, $options);
                break;
            case (in_array('Zend\Session\Storage\StorageInterface', class_implements($type))):
                return new $type($options);
                break;
            default:
                throw new Exception\InvalidArgumentException(sprintf(
                    'Unrecognized type "%s" provided; expects a class implementing %s\StorageInterface',
                    $type,
                    __NAMESPACE__
                ));
        }
    }

    /**
     * Create a storage object from an ArrayStorage class (or a descendent)
     *
     * @param  string       $type
     * @param  array        $options
     * @return ArrayStorage
     */
    protected static function createArrayStorage($type, $options)
    {
        $input         = array();
        $flags         = ArrayObject::ARRAY_AS_PROPS;
        $iteratorClass = 'ArrayIterator';

        if (isset($options['input']) && null !== $options['input']) {
            if (!is_array($options['input'])) {
                throw new Exception\InvalidArgumentException(sprintf(
                    '%s expects the "input" option to be an array; received "%s"',
                    $type,
                    (is_object($options['input']) ? get_class($options['input']) : gettype($options['input']))
                ));
            }
            $input = $options['input'];
        }

        if (isset($options['flags'])) {
            $flags = $options['flags'];
        }

        if (isset($options['iterator_class'])) {
            if (!class_exists($options['iterator_class'])) {
                throw new Exception\InvalidArgumentException(sprintf(
                    '%s expects the "iterator_class" option to be a valid class; received "%s"',
                    $type,
                    (is_object($options['iterator_class']) ? get_class($options['iterator_class']) : gettype($options['iterator_class']))
                ));
            }
            $iteratorClass = $options['iterator_class'];
        }

        return new $type($input, $flags, $iteratorClass);
    }

    /**
     * Create a storage object from a class extending AbstractSessionArrayStorage
     *
     * @param  string                             $type
     * @param  array                              $options
     * @return AbstractSessionArrayStorage
     * @throws Exception\InvalidArgumentException if the input option is invalid
     */
    protected static function createSessionArrayStorage($type, array $options)
    {
        $input = null;
        if (isset($options['input'])) {
            if (null !== $options['input']
                && !is_array($options['input'])
                && !$input instanceof ArrayAccess
            ) {
                throw new Exception\InvalidArgumentException(sprintf(
                    '%s expects the "input" option to be null, an array, or to implement ArrayAccess; received "%s"',
                    $type,
                    (is_object($options['input']) ? get_class($options['input']) : gettype($options['input']))
                ));
            }
            $input = $options['input'];
        }

        return new $type($input);
    }
}
