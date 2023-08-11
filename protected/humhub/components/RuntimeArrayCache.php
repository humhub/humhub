<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\components;

use exceptions\ArrayIndexNotFound;
use humhub\exceptions\InvalidArgumentException;
use humhub\exceptions\InvalidArgumentTypeException;
use humhub\interfaces\ArrayLikeInterface;
use humhub\interfaces\RuntimeCacheInterface;
use humhub\interfaces\RuntimeCacheStorageInterface;
use humhub\interfaces\UniqueIdentifiersInterface;
use humhub\libs\ArrayObject;
use yii\base\BaseObject;
use yii\db\ActiveRecordInterface;

use function extension_loaded;

/**
 * @since 1.15
 *
 * @noinspection MissingPropertyAnnotationsInspection
 */
class RuntimeArrayCache extends RuntimeBaseCache
{
    /**
     * @var RuntimeCacheStorageInterface<string, RuntimeCacheItem>
     */
    protected RuntimeCacheStorageInterface $cache;

    /**
     * @var ArrayLikeInterface<string, RuntimeCacheItem>
     */
    protected ArrayLikeInterface $keys;

    /**
     * @var bool whether [igbinary serialization](https://pecl.php.net/package/igbinary) is available or not.
     */
    protected bool $igBinaryAvailable = false;


    public function __construct($config = [])
    {
        $this->cache = new ArrayObject();
        $this->keys = new ArrayObject();

        parent::__construct($config);
    }


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->igBinaryAvailable = extension_loaded('igbinary');
    }

    protected function &getCache(): RuntimeCacheStorageInterface
    {
        return $this->cache;
    }

    /**
     * @inheritdoc
     */
    protected function &getRulesCache(): ArrayLikeInterface
    {
        return $this->keys;
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        if ($key instanceof RuntimeCacheItem) {
            $key = func_get_arg(1);
            return $this->cache[$key] ?? null;
        }

        return parent::get($key);
    }

    /**
     * @inheritdoc
     *
     * @param string|array|null $key
     */
    public function set($key, $value = null, $duration = null, $dependency = null): bool
    {
        if ($value instanceof RuntimeCacheItem) {
            $this->cache[$key] = $value;
            return true;
        }

        return parent::set($key, $value, $duration, $dependency);
    }

    /**
     * @param mixed $key
     * @param mixed $value
     * @param int $duration ignored!
     * @param null $dependency ignored!
     * @inheritdoc
     */
    public function add($key, $value, $duration = 0, $dependency = null): bool
    {
        if (null === $hashes = $this->buildKeysFromValue($key, $value, __METHOD__)) {
            return false;
        }

        if (is_string($hashes)) {
            $hashes = (array)$hashes;
        }

        $cache = $this->getCache();

        foreach ($hashes as $hash) {
            if ($cache->offsetExists($hash)) {
                return false;
            }
        }

        return $this->setValue($hashes, $value);
    }

    /**
     * @inheritdoc
     */
    public function link($keyFrom, string $keyTo, string ...$to): ?array
    {
        $this->isKeyIncluded($keyFrom, $hash, __METHOD__, [1 => '$keyFrom']);

        $cacheFrom = $this->cache[$hash] ?? null;

        if ($cacheFrom === null) {
            throw new InvalidArgumentException(__METHOD__, [1 => '$keyFrom'], 'existing cache key', $keyFrom);
        }

        return $cacheFrom->addKeys($keyTo, ...$to);
    }

    /**
     * @inheritdoc
     */
    public function &unlink($linkKey, string ...$linkKeys): array
    {
        $linkKeys[] = $linkKey;
        $links = array_flip($linkKeys);

        foreach ($links as $linkKey => &$item) {
            $this->isKeyIncluded($linkKey, $hash, __METHOD__, [1 => '$linkKey']);

            $cacheFrom = $this->cache[$hash] ?? null;

            $item = $cacheFrom === null ? null : $cacheFrom->removeKeys($linkKey) ?? $cacheFrom->getValue(false);
        }

        return $links;
    }

    public function delete($key, $ifNotFound = [ArrayIndexNotFound::class, 'create'])
    {
        if ($key instanceof RuntimeCacheItem) {
            if (func_num_args() === 1) {
                --$this->distinctCount;
                return true;
            }

            unset($this->cache[$ifNotFound]);

            return true;
        }

        return parent::delete($key, $ifNotFound);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($key): bool
    {
        if (null === $key = $this->buildKey($key)) {
            return false;
        }

        return $this->getCache()->offsetExists($key);
    }

    /**
     * @param callable $callback Callback with the following signature: `$callback(RuntimeCacheItem $cacheItem, string $hash): bool;`
     * @param int|null $limit
     * @param int|null $count
     * @param bool $returnCacheItem Internal use.
     *
     * @return array|null
     */
    public function &findByCallback(callable $callback, ?int $limit = -1, ?int &$count = 0, bool $returnCacheItem = false): ?array
    {
        $limit ??= -1;
        $count = 0;

        if ($limit === 0) {
            $result = null;
            return $result;
        }

        $result = [];

        foreach ($this->cache as $hash => $cacheItem) {
            if ($callback($cacheItem, $hash)) {
                $result[$cacheItem->getKey($hash)] = $returnCacheItem ? $cacheItem : $cacheItem->getValue();

                if (++$count === $limit) {
                    return $result;
                }
            }
        }

        return $result;
    }


    /**
     * @inheritdoc
     */
    public function &findByIdentifier(object $object, ?int $limit = 1, ?int &$count = 0, bool $returnCacheItem = false): ?array
    {
        if (!$object instanceof UniqueIdentifiersInterface && !$object instanceof ActiveRecordInterface) {
            throw new InvalidArgumentTypeException(__METHOD__, [1 => '$pattern'], [UniqueIdentifiersInterface::class, ActiveRecordInterface::class], $object);
        }

        $limit ??= -1;
        $count = 0;
        $keys = $this->buildKeysFromValue($object);
        $result = null;

        foreach ($keys as $key => $hash) {
            if ($cacheItem = $this->cache[$hash] ?? null) {
                $result[$key] = $returnCacheItem ? $cacheItem : $cacheItem->getValue();

                if (++$count === $limit) {
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     * @see RuntimeCacheInterface::flushValues()
     */
    protected function &flushValues(?int &$count = 0): ?array
    {
        $items = $this->getAll();
        $count = count($items);

        $this->getCache()->reset();

        return $items;
    }

    /**
     * @inheritdoc
     *
     * @param string|object $key
     *
     * @return false|mixed
     */
    protected function getValue($key)
    {
        if (empty($key)) {
            return false;
        }

        if (is_string($key)) {
            $cacheItem = $this->cache[$key] ?? null;
        } elseif (is_object($key)) {
            $items = $this->find($key, 1, $count);

            return $count === 1 ? reset($items) : false;
        } else {
            return false;
        }


        return $cacheItem ? $cacheItem->getValue() : false;
    }

    /**
     * @inheritdoc
     *
     * @param array<string, string> $hash
     * @param mixed $value
     * @param int $duration @deprecated and Ignored.
     *
     * @return bool
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    protected function setValue($hash, $value, $duration = 0): bool
    {
        if (!$hash) {
            return false;
        }

        if (is_array($hash)) {
            $hashes = $hash;
            $cacheItem = null;

            foreach ($hashes as $hash) {
                if ($cacheItem = $this->cache[$hash] ?? null) {
                    break;
                }
            }
        } else {
            $hashes = [];
            $cacheItem = $this->find($hash, 1, $count, self::FIND_MODE_AUTO, true);
        }

        if ($cacheItem) {
            $cacheItem->setValue($value);
            return true;
        }

        $cacheItem = new RuntimeCacheItem($this, $hashes, ['value' => $value]);

        if (count($cacheItem->getKeys())) {
            if (++$this->distinctCount > $this->getHardLimit()) {
                $this->flush(null);
            }

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function addValue($key, $value, $duration = 0): bool
    {
        if (!$key) {
            return false;
        }

        $cache = $this->getCache();

        foreach ((array)$key as $hash) {
            if ($cache->offsetExists($hash)) {
                return false;
            }
        }

        return $this->setValue($key, $value);
    }

    /**
     * {@inheritdoc}
     * @param string|object|RuntimeCacheItem $key
     */
    protected function deleteValue($key, $originalKey = null, $ifNotFound = [ArrayIndexNotFound::class, 'create'])
    {
        $cacheItem = $key instanceof RuntimeCacheItem ? $key : $this->cache[$key] ?? null;

        if ($cacheItem) {
            $cacheItem->delete();

            $value = $cacheItem->getValue(false);

            /**
             * Check if we have the related record cached in the polymorphic behavior, so we can delete the cache by ID.
             * (This is not fully bullet-proof, as the object might still be saved in the cache, but only under the guid key.)
             */
            if (!$value instanceof BaseObject || !$value->hasMethod('getPolymorphicRelation')) {
                return $value;
            }

            if (
                $model = $value->getPolymorphicRelation(false)
                    ?? $this->get(static::normaliseObjectIdentifier($value->{$value->classAttribute}, $value->{$value->pkAttribute}))
            ) {
                $this->delete($model);
            }

            return $value;
        }

        if (is_callable($ifNotFound)) {
            return $ifNotFound($originalKey ?? $key);
        }

        return $ifNotFound;
    }
}
