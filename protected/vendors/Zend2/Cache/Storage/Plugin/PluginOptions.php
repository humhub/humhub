<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage\Plugin;

use Zend\Cache\Exception;
use Zend\Serializer\Adapter\AdapterInterface as SerializerAdapter;
use Zend\Serializer\Serializer as SerializerFactory;
use Zend\Stdlib\AbstractOptions;

class PluginOptions extends AbstractOptions
{
    /**
     * Used by:
     * - ClearByFactor
     * @var int
     */
    protected $clearingFactor = 0;

    /**
     * Used by:
     * - ExceptionHandler
     * @var callable
     */
    protected $exceptionCallback;

    /**
     * Used by:
     * - IgnoreUserAbort
     * @var bool
     */
    protected $exitOnAbort = true;

    /**
     * Used by:
     * - OptimizeByFactor
     * @var int
     */
    protected $optimizingFactor = 0;

    /**
     * Used by:
     * - Serializer
     * @var string|SerializerAdapter
     */
    protected $serializer;

    /**
     * Used by:
     * - Serializer
     * @var array
     */
    protected $serializerOptions = array();

    /**
     * Used by:
     * - ExceptionHandler
     * @var bool
     */
    protected $throwExceptions = true;

    /**
     * Set automatic clearing factor
     *
     * Used by:
     * - ClearExpiredByFactor
     *
     * @param  int $clearingFactor
     * @return PluginOptions
     */
    public function setClearingFactor($clearingFactor)
    {
        $this->clearingFactor = $this->normalizeFactor($clearingFactor);
        return $this;
    }

    /**
     * Get automatic clearing factor
     *
     * Used by:
     * - ClearExpiredByFactor
     *
     * @return int
     */
    public function getClearingFactor()
    {
        return $this->clearingFactor;
    }

    /**
     * Set callback to call on intercepted exception
     *
     * Used by:
     * - ExceptionHandler
     *
     * @param  callable $exceptionCallback
     * @throws Exception\InvalidArgumentException
     * @return PluginOptions
     */
    public function setExceptionCallback($exceptionCallback)
    {
        if ($exceptionCallback !== null && !is_callable($exceptionCallback, true)) {
            throw new Exception\InvalidArgumentException('Not a valid callback');
        }
        $this->exceptionCallback = $exceptionCallback;
        return $this;
    }

    /**
     * Get callback to call on intercepted exception
     *
     * Used by:
     * - ExceptionHandler
     *
     * @return null|callable
     */
    public function getExceptionCallback()
    {
        return $this->exceptionCallback;
    }

    /**
     * Exit if connection aborted and ignore_user_abort is disabled.
     *
     * @param  bool $exitOnAbort
     * @return PluginOptions
     */
    public function setExitOnAbort($exitOnAbort)
    {
        $this->exitOnAbort = (bool) $exitOnAbort;
        return $this;
    }

    /**
     * Exit if connection aborted and ignore_user_abort is disabled.
     *
     * @return bool
     */
    public function getExitOnAbort()
    {
        return $this->exitOnAbort;
    }

    /**
     * Set automatic optimizing factor
     *
     * Used by:
     * - OptimizeByFactor
     *
     * @param  int $optimizingFactor
     * @return PluginOptions
     */
    public function setOptimizingFactor($optimizingFactor)
    {
        $this->optimizingFactor = $this->normalizeFactor($optimizingFactor);
        return $this;
    }

    /**
     * Set automatic optimizing factor
     *
     * Used by:
     * - OptimizeByFactor
     *
     * @return int
     */
    public function getOptimizingFactor()
    {
        return $this->optimizingFactor;
    }

    /**
     * Set serializer
     *
     * Used by:
     * - Serializer
     *
     * @param  string|SerializerAdapter $serializer
     * @throws Exception\InvalidArgumentException
     * @return Serializer
     */
    public function setSerializer($serializer)
    {
        if (!is_string($serializer) && !$serializer instanceof SerializerAdapter) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects either a string serializer name or Zend\Serializer\Adapter\AdapterInterface instance; '
                . 'received "%s"',
                __METHOD__,
                (is_object($serializer) ? get_class($serializer) : gettype($serializer))
            ));
        }
        $this->serializer = $serializer;
        return $this;
    }

    /**
     * Get serializer
     *
     * Used by:
     * - Serializer
     *
     * @return SerializerAdapter
     */
    public function getSerializer()
    {
        if (!$this->serializer instanceof SerializerAdapter) {
            // use default serializer
            if (!$this->serializer) {
                $this->setSerializer(SerializerFactory::getDefaultAdapter());
            // instantiate by class name + serializer_options
            } else {
                $options = $this->getSerializerOptions();
                $this->setSerializer(SerializerFactory::factory($this->serializer, $options));
            }
        }
        return $this->serializer;
    }

    /**
     * Set configuration options for instantiating a serializer adapter
     *
     * Used by:
     * - Serializer
     *
     * @param  mixed $serializerOptions
     * @return PluginOptions
     */
    public function setSerializerOptions($serializerOptions)
    {
        $this->serializerOptions = $serializerOptions;
        return $this;
    }

    /**
     * Get configuration options for instantiating a serializer adapter
     *
     * Used by:
     * - Serializer
     *
     * @return array
     */
    public function getSerializerOptions()
    {
        return $this->serializerOptions;
    }

    /**
     * Set flag indicating we should re-throw exceptions
     *
     * Used by:
     * - ExceptionHandler
     *
     * @param  bool $throwExceptions
     * @return PluginOptions
     */
    public function setThrowExceptions($throwExceptions)
    {
        $this->throwExceptions = (bool) $throwExceptions;
        return $this;
    }

    /**
     * Should we re-throw exceptions?
     *
     * Used by:
     * - ExceptionHandler
     *
     * @return bool
     */
    public function getThrowExceptions()
    {
        return $this->throwExceptions;
    }

    /**
     * Normalize a factor
     *
     * Cast to int and ensure we have a value greater than zero.
     *
     * @param  int $factor
     * @return int
     * @throws Exception\InvalidArgumentException
     */
    protected function normalizeFactor($factor)
    {
        $factor = (int) $factor;
        if ($factor < 0) {
            throw new Exception\InvalidArgumentException(
                "Invalid factor '{$factor}': must be greater or equal 0"
            );
        }
        return $factor;
    }
}
