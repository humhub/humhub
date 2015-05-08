<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage\Adapter;

use stdClass;
use Traversable;
use Zend\Cache\Exception;
use Zend\Cache\Storage\AvailableSpaceCapableInterface;
use Zend\Cache\Storage\Capabilities;
use Zend\Cache\Storage\ClearByNamespaceInterface;
use Zend\Cache\Storage\ClearByPrefixInterface;
use Zend\Cache\Storage\FlushableInterface;
use Zend\Cache\Storage\IterableInterface;
use Zend\Cache\Storage\TotalSpaceCapableInterface;

class XCache extends AbstractAdapter implements
    AvailableSpaceCapableInterface,
    ClearByNamespaceInterface,
    ClearByPrefixInterface,
    FlushableInterface,
    IterableInterface,
    TotalSpaceCapableInterface
{

    /**
     * Backup HTTP authentication properties of $_SERVER array
     *
     * @var array
     */
    protected $backupAuth = array();

    /**
     * Total space in bytes
     *
     * @var int|float
     */
    protected $totalSpace;

    /**
     * Constructor
     *
     * @param  null|array|Traversable|ApcOptions $options
     * @throws Exception\ExceptionInterface
     */
    public function __construct($options = null)
    {
        if (!extension_loaded('xcache')) {
            throw new Exception\ExtensionNotLoadedException('Missing ext/xcache');
        }

        if (PHP_SAPI == 'cli') {
            throw new Exception\ExtensionNotLoadedException(
                "ext/xcache isn't available on SAPI 'cli'"
            );
        }

        if (ini_get('xcache.var_size') <= 0) {
            throw new Exception\ExtensionNotLoadedException(
                "ext/xcache is disabled - see 'xcache.var_size'"
            );
        }

        parent::__construct($options);
    }

    /* options */

    /**
     * Set options.
     *
     * @param  array|Traversable|ApcOptions $options
     * @return XCache
     * @see    getOptions()
     */
    public function setOptions($options)
    {
        if (!$options instanceof XCacheOptions) {
            $options = new XCacheOptions($options);
        }

        return parent::setOptions($options);
    }

    /**
     * Get options.
     *
     * @return XCacheOptions
     * @see    setOptions()
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions(new XCacheOptions());
        }
        return $this->options;
    }

    /* TotalSpaceCapableInterface */

    /**
     * Get total space in bytes
     *
     * @return int|float
     */
    public function getTotalSpace()
    {
        if ($this->totalSpace === null) {
            $this->totalSpace = 0;

            $this->initAdminAuth();
            $cnt = xcache_count(XC_TYPE_VAR);
            for ($i=0; $i < $cnt; $i++) {
                $info = xcache_info(XC_TYPE_VAR, $i);
                $this->totalSpace+= $info['size'];
            }
            $this->resetAdminAuth();
        }

        return $this->totalSpace;
    }

    /* AvailableSpaceCapableInterface */

    /**
     * Get available space in bytes
     *
     * @return int|float
     */
    public function getAvailableSpace()
    {
        $availableSpace = 0;

        $this->initAdminAuth();
        $cnt = xcache_count(XC_TYPE_VAR);
        for ($i = 0; $i < $cnt; $i++) {
            $info = xcache_info(XC_TYPE_VAR, $i);
            $availableSpace+= $info['avail'];
        }
        $this->resetAdminAuth();

        return $availableSpace;
    }


    /* ClearByNamespaceInterface */

    /**
     * Remove items by given namespace
     *
     * @param string $namespace
     * @return bool
     */
    public function clearByNamespace($namespace)
    {
        $namespace = (string) $namespace;
        if ($namespace === '') {
            throw new Exception\InvalidArgumentException('No namespace given');
        }

        $options = $this->getOptions();
        $prefix  = $namespace . $options->getNamespaceSeparator();

        xcache_unset_by_prefix($prefix);
        return true;
    }

    /* ClearByPrefixInterface */

    /**
     * Remove items matching given prefix
     *
     * @param string $prefix
     * @return bool
     */
    public function clearByPrefix($prefix)
    {
        $prefix = (string) $prefix;
        if ($prefix === '') {
            throw new Exception\InvalidArgumentException('No prefix given');
        }

        $options   = $this->getOptions();
        $namespace = $options->getNamespace();
        $prefix    = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator() . $prefix;

        xcache_unset_by_prefix($prefix);
        return true;
    }

    /* FlushableInterface */

    /**
     * Flush the whole storage
     *
     * @return bool
     */
    public function flush()
    {
        $this->initAdminAuth();
        $cnt = xcache_count(XC_TYPE_VAR);
        for ($i = 0; $i < $cnt; $i++) {
            xcache_clear_cache(XC_TYPE_VAR, $i);
        }
        $this->resetAdminAuth();

        return true;
    }

    /* IterableInterface */

    /**
     * Get the storage iterator
     *
     * @return KeyListIterator
     */
    public function getIterator()
    {

        $options   = $this->getOptions();
        $namespace = $options->getNamespace();
        $keys      = array();

        $this->initAdminAuth();

        if ($namespace === '') {
            $cnt = xcache_count(XC_TYPE_VAR);
            for ($i=0; $i < $cnt; $i++) {
                $list = xcache_list(XC_TYPE_VAR, $i);
                foreach ($list['cache_list'] as & $item) {
                    $keys[] = $item['name'];
                }
            }
        } else {

            $prefix  = $namespace . $options->getNamespaceSeparator();
            $prefixL = strlen($prefix);

            $cnt = xcache_count(XC_TYPE_VAR);
            for ($i=0; $i < $cnt; $i++) {
                $list = xcache_list(XC_TYPE_VAR, $i);
                foreach ($list['cache_list'] as & $item) {
                    $keys[] = substr($item['name'], $prefixL);
                }
            }
        }

        $this->resetAdminAuth();

        return new KeyListIterator($this, $keys);
    }

    /* reading */

    /**
     * Internal method to get an item.
     *
     * @param  string  $normalizedKey
     * @param  bool $success
     * @param  mixed   $casToken
     * @return mixed Data on success, null on failure
     * @throws Exception\ExceptionInterface
     */
    protected function internalGetItem(& $normalizedKey, & $success = null, & $casToken = null)
    {
        $options     = $this->getOptions();
        $namespace   = $options->getNamespace();
        $prefix      = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();
        $internalKey = $prefix . $normalizedKey;

        $result  = xcache_get($internalKey);
        $success = ($result !== null);

        if ($success) {
            $casToken = $result;
        }

        return $result;
    }

    /**
     * Internal method to test if an item exists.
     *
     * @param  string $normalizedKey
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    protected function internalHasItem(& $normalizedKey)
    {
        $options   = $this->getOptions();
        $namespace = $options->getNamespace();
        $prefix    = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();
        return xcache_isset($prefix . $normalizedKey);
    }

    /**
     * Get metadata of an item.
     *
     * @param  string $normalizedKey
     * @return array|bool Metadata on success, false on failure
     * @throws Exception\ExceptionInterface
     */
    protected function internalGetMetadata(& $normalizedKey)
    {
        $options     = $this->getOptions();
        $namespace   = $options->getNamespace();
        $prefix      = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();
        $internalKey = $prefix . $normalizedKey;

        if (xcache_isset($internalKey)) {

            $this->initAdminAuth();
            $cnt = xcache_count(XC_TYPE_VAR);
            for ($i=0; $i < $cnt; $i++) {
                $list = xcache_list(XC_TYPE_VAR, $i);
                foreach ($list['cache_list'] as & $metadata) {
                    if ($metadata['name'] === $internalKey) {
                        $this->normalizeMetadata($metadata);
                        return $metadata;
                    }
                }
            }
            $this->resetAdminAuth();
        }

        return false;
    }

    /* writing */

    /**
     * Internal method to store an item.
     *
     * @param  string $normalizedKey
     * @param  mixed  $value
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    protected function internalSetItem(& $normalizedKey, & $value)
    {
        $options     = $this->getOptions();
        $namespace   = $options->getNamespace();
        $prefix      = ($options === '') ? '' : $namespace . $options->getNamespaceSeparator();
        $internalKey = $prefix . $normalizedKey;
        $ttl         = $options->getTtl();

        if (!xcache_set($internalKey, $value, $ttl)) {
            $type = is_object($value) ? get_class($value) : gettype($value);
            throw new Exception\RuntimeException(
                "xcache_set('{$internalKey}', <{$type}>, {$ttl}) failed"
            );
        }

        return true;
    }

    /**
     * Internal method to remove an item.
     *
     * @param  string $normalizedKey
     * @return bool
     * @throws Exception\ExceptionInterface
     */
    protected function internalRemoveItem(& $normalizedKey)
    {
        $options     = $this->getOptions();
        $namespace   = $options->getNamespace();
        $prefix      = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();
        $internalKey = $prefix . $normalizedKey;

        return xcache_unset($internalKey);
    }

    /**
     * Internal method to increment an item.
     *
     * @param  string $normalizedKey
     * @param  int    $value
     * @return int|bool The new value on success, false on failure
     * @throws Exception\ExceptionInterface
     */
    protected function internalIncrementItem(& $normalizedKey, & $value)
    {
        $options     = $this->getOptions();
        $namespace   = $options->getNamespace();
        $prefix      = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();
        $internalKey = $prefix . $normalizedKey;
        $ttl         = $options->getTtl();
        $value       = (int) $value;

        return xcache_inc($internalKey, $value, $ttl);
    }

    /**
     * Internal method to decrement an item.
     *
     * @param  string $normalizedKey
     * @param  int    $value
     * @return int|bool The new value on success, false on failure
     * @throws Exception\ExceptionInterface
     */
    protected function internalDecrementItem(& $normalizedKey, & $value)
    {
        $options     = $this->getOptions();
        $namespace   = $options->getNamespace();
        $prefix      = ($namespace === '') ? '' : $namespace . $options->getNamespaceSeparator();
        $internalKey = $prefix . $normalizedKey;
        $ttl         = $options->getTtl();
        $value       = (int) $value;

        return xcache_dec($internalKey, $value, $ttl);
    }

    /* status */

    /**
     * Internal method to get capabilities of this adapter
     *
     * @return Capabilities
     */
    protected function internalGetCapabilities()
    {
        if ($this->capabilities === null) {
            $marker       = new stdClass();
            $capabilities = new Capabilities(
                $this,
                $marker,
                array(
                    'supportedDatatypes' => array(
                        'NULL'     => false,
                        'boolean'  => true,
                        'integer'  => true,
                        'double'   => true,
                        'string'   => true,
                        'array'    => true,
                        'object'   => 'object',
                        'resource' => false,
                    ),
                    'supportedMetadata' => array(
                        'internal_key',
                        'size', 'refcount', 'hits',
                        'ctime', 'atime', 'hvalue',
                    ),
                    'minTtl'             => 1,
                    'maxTtl'             => (int)ini_get('xcache.var_maxttl'),
                    'staticTtl'          => true,
                    'ttlPrecision'       => 1,
                    'useRequestTime'     => true,
                    'expiredRead'        => false,
                    'maxKeyLength'       => 5182,
                    'namespaceIsPrefix'  => true,
                    'namespaceSeparator' => $this->getOptions()->getNamespaceSeparator(),
                )
            );

            // update namespace separator on change option
            $this->getEventManager()->attach('option', function ($event) use ($capabilities, $marker) {
                $params = $event->getParams();

                if (isset($params['namespace_separator'])) {
                    $capabilities->setNamespaceSeparator($marker, $params['namespace_separator']);
                }
            });

            $this->capabilities     = $capabilities;
            $this->capabilityMarker = $marker;
        }

        return $this->capabilities;
    }

    /* internal */

    /**
     * Init authentication before calling admin functions
     *
     * @return void
     */
    protected function initAdminAuth()
    {
        $options = $this->getOptions();

        if ($options->getAdminAuth()) {
            $adminUser = $options->getAdminUser();
            $adminPass = $options->getAdminPass();

            // backup HTTP authentication properties
            if (isset($_SERVER['PHP_AUTH_USER'])) {
                $this->backupAuth['PHP_AUTH_USER'] = $_SERVER['PHP_AUTH_USER'];
            }
            if (isset($_SERVER['PHP_AUTH_PW'])) {
                $this->backupAuth['PHP_AUTH_PW'] = $_SERVER['PHP_AUTH_PW'];
            }

            // set authentication
            $_SERVER['PHP_AUTH_USER'] = $adminUser;
            $_SERVER['PHP_AUTH_PW']   = $adminPass;
        }
    }

    /**
     * Reset authentication after calling admin functions
     *
     * @return void
     */
    protected function resetAdminAuth()
    {
        unset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        $_SERVER = $this->backupAuth + $_SERVER;
        $this->backupAuth = array();
    }

    /**
     * Normalize metadata to work with XCache
     *
     * @param  array $metadata
     */
    protected function normalizeMetadata(array & $metadata)
    {
        $metadata['internal_key'] = &$metadata['name'];
        unset($metadata['name']);
    }
}
