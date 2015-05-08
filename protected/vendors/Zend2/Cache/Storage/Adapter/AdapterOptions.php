<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage\Adapter;

use ArrayObject;
use Zend\Cache\Exception;
use Zend\Cache\Storage\Event;
use Zend\Cache\Storage\StorageInterface;
use Zend\EventManager\EventsCapableInterface;
use Zend\Stdlib\AbstractOptions;
use Zend\Stdlib\ErrorHandler;

/**
 * Unless otherwise marked, all options in this class affect all adapters.
 */
class AdapterOptions extends AbstractOptions
{

    /**
     * The adapter using these options
     *
     * @var null|Filesystem
     */
    protected $adapter;

    /**
     * Validate key against pattern
     *
     * @var string
     */
    protected $keyPattern = '';

    /**
     * Namespace option
     *
     * @var string
     */
    protected $namespace = 'zfcache';

    /**
     * Readable option
     *
     * @var bool
     */
    protected $readable = true;

    /**
     * TTL option
     *
     * @var int|float 0 means infinite or maximum of adapter
     */
    protected $ttl = 0;

    /**
     * Writable option
     *
     * @var bool
     */
    protected $writable = true;

    /**
     * Adapter using this instance
     *
     * @param  StorageInterface|null $adapter
     * @return AdapterOptions
     */
    public function setAdapter(StorageInterface $adapter = null)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * Set key pattern
     *
     * @param  null|string $keyPattern
     * @throws Exception\InvalidArgumentException
     * @return AdapterOptions
     */
    public function setKeyPattern($keyPattern)
    {
        $keyPattern = (string) $keyPattern;
        if ($this->keyPattern !== $keyPattern) {
            // validate pattern
            if ($keyPattern !== '') {
                ErrorHandler::start(E_WARNING);
                $result = preg_match($keyPattern, '');
                $error = ErrorHandler::stop();
                if ($result === false) {
                    throw new Exception\InvalidArgumentException(sprintf(
                        'Invalid pattern "%s"%s',
                        $keyPattern,
                        ($error ? ': ' . $error->getMessage() : '')
                    ), 0, $error);
                }
            }

            $this->triggerOptionEvent('key_pattern', $keyPattern);
            $this->keyPattern = $keyPattern;
        }

        return $this;
    }

    /**
     * Get key pattern
     *
     * @return string
     */
    public function getKeyPattern()
    {
        return $this->keyPattern;
    }

    /**
     * Set namespace.
     *
     * @param  string $namespace
     * @return AdapterOptions
     */
    public function setNamespace($namespace)
    {
        $namespace = (string) $namespace;
        if ($this->namespace !== $namespace) {
            $this->triggerOptionEvent('namespace', $namespace);
            $this->namespace = $namespace;
        }

        return $this;
    }

    /**
     * Get namespace
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Enable/Disable reading data from cache.
     *
     * @param  bool $readable
     * @return AbstractAdapter
     */
    public function setReadable($readable)
    {
        $readable = (bool) $readable;
        if ($this->readable !== $readable) {
            $this->triggerOptionEvent('readable', $readable);
            $this->readable = $readable;
        }
        return $this;
    }

    /**
     * If reading data from cache enabled.
     *
     * @return bool
     */
    public function getReadable()
    {
        return $this->readable;
    }

    /**
     * Set time to live.
     *
     * @param  int|float $ttl
     * @return AdapterOptions
     */
    public function setTtl($ttl)
    {
        $this->normalizeTtl($ttl);
        if ($this->ttl !== $ttl) {
            $this->triggerOptionEvent('ttl', $ttl);
            $this->ttl = $ttl;
        }
        return $this;
    }

    /**
     * Get time to live.
     *
     * @return float
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * Enable/Disable writing data to cache.
     *
     * @param  bool $writable
     * @return AdapterOptions
     */
    public function setWritable($writable)
    {
        $writable = (bool) $writable;
        if ($this->writable !== $writable) {
            $this->triggerOptionEvent('writable', $writable);
            $this->writable = $writable;
        }
        return $this;
    }

    /**
     * If writing data to cache enabled.
     *
     * @return bool
     */
    public function getWritable()
    {
        return $this->writable;
    }

    /**
     * Triggers an option event if this options instance has a connection to
     * an adapter implements EventsCapableInterface.
     *
     * @param string $optionName
     * @param mixed  $optionValue
     * @return void
     */
    protected function triggerOptionEvent($optionName, $optionValue)
    {
        if ($this->adapter instanceof EventsCapableInterface) {
            $event = new Event('option', $this->adapter, new ArrayObject(array($optionName => $optionValue)));
            $this->adapter->getEventManager()->trigger($event);
        }
    }

    /**
     * Validates and normalize a TTL.
     *
     * @param  int|float $ttl
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    protected function normalizeTtl(&$ttl)
    {
        if (!is_int($ttl)) {
            $ttl = (float) $ttl;

            // convert to int if possible
            if ($ttl === (float) (int) $ttl) {
                $ttl = (int) $ttl;
            }
        }

        if ($ttl < 0) {
             throw new Exception\InvalidArgumentException("TTL can't be negative");
        }
    }
}
