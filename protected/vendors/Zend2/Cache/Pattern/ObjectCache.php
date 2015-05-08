<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Pattern;

use Zend\Cache\Exception;

class ObjectCache extends CallbackCache
{
    /**
     * Set options
     *
     * @param  PatternOptions $options
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    public function setOptions(PatternOptions $options)
    {
        parent::setOptions($options);

        if (!$options->getObject()) {
            throw new Exception\InvalidArgumentException("Missing option 'object'");
        } elseif (!$options->getStorage()) {
            throw new Exception\InvalidArgumentException("Missing option 'storage'");
        }
    }

    /**
     * Call and cache a class method
     *
     * @param  string $method  Method name to call
     * @param  array  $args    Method arguments
     * @return mixed
     * @throws Exception\RuntimeException
     * @throws \Exception
     */
    public function call($method, array $args = array())
    {
        $options = $this->getOptions();
        $object  = $options->getObject();
        $method  = strtolower($method);

        // handle magic methods
        switch ($method) {
            case '__set':
                $property = array_shift($args);
                $value    = array_shift($args);

                $object->{$property} = $value;

                if (!$options->getObjectCacheMagicProperties()
                    || property_exists($object, $property)
                ) {
                    // no caching if property isn't magic
                    // or caching magic properties is disabled
                    return;
                }

                // remove cached __get and __isset
                $removeKeys = null;
                if (method_exists($object, '__get')) {
                    $removeKeys[] = $this->generateKey('__get', array($property));
                }
                if (method_exists($object, '__isset')) {
                    $removeKeys[] = $this->generateKey('__isset', array($property));
                }
                if ($removeKeys) {
                    $options->getStorage()->removeItems($removeKeys);
                }
                return;

            case '__get':
                $property = array_shift($args);

                if (!$options->getObjectCacheMagicProperties()
                    || property_exists($object, $property)
                ) {
                    // no caching if property isn't magic
                    // or caching magic properties is disabled
                    return $object->{$property};
                }

                array_unshift($args, $property);
                return parent::call(array($object, '__get'), $args);

           case '__isset':
                $property = array_shift($args);

                if (!$options->getObjectCacheMagicProperties()
                    || property_exists($object, $property)
                ) {
                    // no caching if property isn't magic
                    // or caching magic properties is disabled
                    return isset($object->{$property});
                }

                return parent::call(array($object, '__isset'), array($property));

            case '__unset':
                $property = array_shift($args);

                unset($object->{$property});

                if (!$options->getObjectCacheMagicProperties()
                    || property_exists($object, $property)
                ) {
                    // no caching if property isn't magic
                    // or caching magic properties is disabled
                    return;
                }

                // remove previous cached __get and __isset calls
                $removeKeys = null;
                if (method_exists($object, '__get')) {
                    $removeKeys[] = $this->generateKey('__get', array($property));
                }
                if (method_exists($object, '__isset')) {
                    $removeKeys[] = $this->generateKey('__isset', array($property));
                }
                if ($removeKeys) {
                    $options->getStorage()->removeItems($removeKeys);
                }
                return;
        }

        $cache = $options->getCacheByDefault();
        if ($cache) {
            $cache = !in_array($method, $options->getObjectNonCacheMethods());
        } else {
            $cache = in_array($method, $options->getObjectCacheMethods());
        }

        if (!$cache) {
            if ($args) {
                return call_user_func_array(array($object, $method), $args);
            }
            return $object->{$method}();
        }

        return parent::call(array($object, $method), $args);
    }

    /**
     * Generate a unique key in base of a key representing the callback part
     * and a key representing the arguments part.
     *
     * @param  string     $method  The method
     * @param  array      $args    Callback arguments
     * @return string
     * @throws Exception\RuntimeException
     */
    public function generateKey($method, array $args = array())
    {
        return $this->generateCallbackKey(
            array($this->getOptions()->getObject(), $method),
            $args
        );
    }

    /**
     * Generate a unique key in base of a key representing the callback part
     * and a key representing the arguments part.
     *
     * @param  callable   $callback  A valid callback
     * @param  array      $args      Callback arguments
     * @return string
     * @throws Exception\RuntimeException
     */
    protected function generateCallbackKey($callback, array $args = array())
    {
        $callbackKey = md5($this->getOptions()->getObjectKey() . '::' . strtolower($callback[1]));
        $argumentKey = $this->generateArgumentsKey($args);
        return $callbackKey . $argumentKey;
    }

    /**
     * Class method call handler
     *
     * @param  string $method  Method name to call
     * @param  array  $args    Method arguments
     * @return mixed
     * @throws Exception\RuntimeException
     * @throws \Exception
     */
    public function __call($method, array $args)
    {
        return $this->call($method, $args);
    }

    /**
     * Writing data to properties.
     *
     * NOTE:
     * Magic properties will be cached too if the option cacheMagicProperties
     * is enabled and the property doesn't exist in real. If so it calls __set
     * and removes cached data of previous __get and __isset calls.
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     * @see    http://php.net/manual/language.oop5.overloading.php#language.oop5.overloading.members
     */
    public function __set($name, $value)
    {
        return $this->call('__set', array($name, $value));
    }

    /**
     * Reading data from properties.
     *
     * NOTE:
     * Magic properties will be cached too if the option cacheMagicProperties
     * is enabled and the property doesn't exist in real. If so it calls __get.
     *
     * @param  string $name
     * @return mixed
     * @see http://php.net/manual/language.oop5.overloading.php#language.oop5.overloading.members
     */
    public function __get($name)
    {
        return $this->call('__get', array($name));
    }

    /**
     * Checking existing properties.
     *
     * NOTE:
     * Magic properties will be cached too if the option cacheMagicProperties
     * is enabled and the property doesn't exist in real. If so it calls __get.
     *
     * @param  string $name
     * @return bool
     * @see    http://php.net/manual/language.oop5.overloading.php#language.oop5.overloading.members
     */
    public function __isset($name)
    {
        return $this->call('__isset', array($name));
    }

    /**
     * Unseting a property.
     *
     * NOTE:
     * Magic properties will be cached too if the option cacheMagicProperties
     * is enabled and the property doesn't exist in real. If so it removes
     * previous cached __isset and __get calls.
     *
     * @param  string $name
     * @return void
     * @see    http://php.net/manual/language.oop5.overloading.php#language.oop5.overloading.members
     */
    public function __unset($name)
    {
        return $this->call('__unset', array($name));
    }

    /**
     * Handle casting to string
     *
     * @return string
     * @see    http://php.net/manual/language.oop5.magic.php#language.oop5.magic.tostring
     */
    public function __toString()
    {
        return $this->call('__toString');
    }

    /**
     * Handle invoke calls
     *
     * @return mixed
     * @see    http://php.net/manual/language.oop5.magic.php#language.oop5.magic.invoke
     */
    public function __invoke()
    {
        return $this->call('__invoke', func_get_args());
    }
}
