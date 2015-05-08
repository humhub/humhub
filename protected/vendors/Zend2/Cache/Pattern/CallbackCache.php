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
use Zend\Stdlib\ErrorHandler;

class CallbackCache extends AbstractPattern
{
    /**
     * Set options
     *
     * @param  PatternOptions $options
     * @return CallbackCache
     * @throws Exception\InvalidArgumentException if missing storage option
     */
    public function setOptions(PatternOptions $options)
    {
        parent::setOptions($options);

        if (!$options->getStorage()) {
            throw new Exception\InvalidArgumentException("Missing option 'storage'");
        }
        return $this;
    }

    /**
     * Call the specified callback or get the result from cache
     *
     * @param  callable   $callback  A valid callback
     * @param  array      $args      Callback arguments
     * @return mixed Result
     * @throws Exception\RuntimeException if invalid cached data
     * @throws \Exception
     */
    public function call($callback, array $args = array())
    {
        $options = $this->getOptions();
        $storage = $options->getStorage();
        $success = null;
        $key     = $this->generateCallbackKey($callback, $args);
        $result  = $storage->getItem($key, $success);
        if ($success) {
            if (!array_key_exists(0, $result)) {
                throw new Exception\RuntimeException("Invalid cached data for key '{$key}'");
            }

            echo isset($result[1]) ? $result[1] : '';
            return $result[0];
        }

        $cacheOutput = $options->getCacheOutput();
        if ($cacheOutput) {
            ob_start();
            ob_implicit_flush(false);
        }

        // TODO: do not cache on errors using [set|restore]_error_handler

        try {
            if ($args) {
                $ret = call_user_func_array($callback, $args);
            } else {
                $ret = call_user_func($callback);
            }
        } catch (\Exception $e) {
            if ($cacheOutput) {
                ob_end_flush();
            }
            throw $e;
        }

        if ($cacheOutput) {
            $data = array($ret, ob_get_flush());
        } else {
            $data = array($ret);
        }

        $storage->setItem($key, $data);

        return $ret;
    }

    /**
     * function call handler
     *
     * @param  string $function  Function name to call
     * @param  array  $args      Function arguments
     * @return mixed
     * @throws Exception\RuntimeException
     * @throws \Exception
     */
    public function __call($function, array $args)
    {
        return $this->call($function, $args);
    }

    /**
     * Generate a unique key in base of a key representing the callback part
     * and a key representing the arguments part.
     *
     * @param  callable   $callback  A valid callback
     * @param  array      $args      Callback arguments
     * @return string
     * @throws Exception\RuntimeException
     * @throws Exception\InvalidArgumentException
     */
    public function generateKey($callback, array $args = array())
    {
        return $this->generateCallbackKey($callback, $args);
    }

    /**
     * Generate a unique key in base of a key representing the callback part
     * and a key representing the arguments part.
     *
     * @param  callable   $callback  A valid callback
     * @param  array      $args      Callback arguments
     * @throws Exception\RuntimeException if callback not serializable
     * @throws Exception\InvalidArgumentException if invalid callback
     * @return string
     */
    protected function generateCallbackKey($callback, array $args)
    {
        if (!is_callable($callback, false, $callbackKey)) {
            throw new Exception\InvalidArgumentException('Invalid callback');
        }

        // functions, methods and classnames are case-insensitive
        $callbackKey = strtolower($callbackKey);

        // generate a unique key of object callbacks
        if (is_object($callback)) { // Closures & __invoke
            $object = $callback;
        } elseif (isset($callback[0])) { // array($object, 'method')
            $object = $callback[0];
        }
        if (isset($object)) {
            ErrorHandler::start();
            try {
                $serializedObject = serialize($object);
            } catch (\Exception $e) {
                ErrorHandler::stop();
                throw new Exception\RuntimeException(
                    "Can't serialize callback: see previous exception", 0, $e
                );
            }
            $error = ErrorHandler::stop();

            if (!$serializedObject) {
                throw new Exception\RuntimeException(sprintf(
                    'Cannot serialize callback%s',
                    ($error ? ': ' . $error->getMessage() : '')
                ), 0, $error);
            }
            $callbackKey.= $serializedObject;
        }

        return md5($callbackKey) . $this->generateArgumentsKey($args);
    }

    /**
     * Generate a unique key of the argument part.
     *
     * @param  array $args
     * @throws Exception\RuntimeException
     * @return string
     */
    protected function generateArgumentsKey(array $args)
    {
        if (!$args) {
            return '';
        }

        ErrorHandler::start();
        try {
            $serializedArgs = serialize(array_values($args));
        } catch (\Exception $e) {
            ErrorHandler::stop();
            throw new Exception\RuntimeException(
                "Can't serialize arguments: see previous exception"
            , 0, $e);
        }
        $error = ErrorHandler::stop();

        if (!$serializedArgs) {
            throw new Exception\RuntimeException(sprintf(
                'Cannot serialize arguments%s',
                ($error ? ': ' . $error->getMessage() : '')
            ), 0, $error);
        }

        return md5($serializedArgs);
    }
}
