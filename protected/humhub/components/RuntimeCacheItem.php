<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\components;

use ArrayAccess;
use Countable;
use humhub\interfaces\RuntimeCacheInterface;
use Iterator;
use WeakReference;
use yii\base\BaseObject;

/**
 *
 * @property mixed $value
 * @property-read int $reads
 * @property-read int $writes
 * @property-read array $keys
 * @property-read array $hashes
 *
 * @since 1.15
 */
class RuntimeCacheItem extends BaseObject implements ArrayAccess, Iterator, Countable
{
    public const STATE_INITIALIZING = 0;
    public const STATE_INITIALIZED = 1;
    public const STATE_VALUE_SET = 2;
    public const STATE_VALUE_UPDATED = 3;
    public const STATE_VALUE_DELETING = 4;
    public const STATE_VALUE_DELETED = 5;

    protected WeakReference $cache;
    protected $value;
    protected array $keys = [];
    protected int $reads = 0;
    protected int $writes = 0;
    protected int $created;
    protected int $state = self::STATE_INITIALIZING;

    /**
     * @param RuntimeCacheInterface $cache
     * @param array $keys
     * @param array|null $config
     */
    public function __construct(RuntimeCacheInterface $cache, array $keys, ?array $config = null)
    {
        $this->created = $cache->getPointOfTime();
        $this->cache = WeakReference::create($cache);
        $this->keys = $keys;

        parent::__construct($config);
    }

    public function __isset($name)
    {
        switch ($name) {
            case  'value':
                return $this->hasValue();

            case  'key':
                return count($this->keys);
        }

        return parent::__isset($name);
    }

    public function init()
    {
        if ($this->state < self::STATE_INITIALIZED) {
            $this->state = self::STATE_INITIALIZED;
        }
    }

    public function delete(): int
    {
        $this->state = self::STATE_VALUE_DELETING;

        if ($deleted = count($this->keys)) {
           /** @noinspection PhpParamsInspection */
            $this->removeKeys('', $this->keys);

            return $deleted;
        }

        $this->state = self::STATE_VALUE_DELETED;

        $this->getCache()->delete($this);

        return $deleted;
    }

    protected function getCache(): RuntimeCacheInterface
    {
        return $this->cache->get();
    }

    /**
     * @return int
     */
    public function getCreated(): int
    {
        return $this->created;
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @return mixed
     */
    public function getValue(bool $countAsRead = true)
    {
        $this->reads += (int)$countAsRead;

        return $this->value;
    }

    /**
     * @param mixed $value
     *
     * @return RuntimeCacheItem
     */
    public function setValue($value): RuntimeCacheItem
    {
        $cache = $this->getCache();
        $oldKeys = $this->value ? $cache->buildKeysFromValue($this->value) ?? [] : [];
        $newKeys = $cache->buildKeysFromValue($value) ?? [];

        if ($this->writes === 0) {
            $newKeys += $this->keys;
            $this->state = self::STATE_VALUE_SET;
        } else {
            $this->state = self::STATE_VALUE_UPDATED;
        }

        $this->writes++;
        $this->value = $value;

        $keysAdded = array_diff_key($newKeys, $oldKeys);
        $keysRemoved = array_diff_key($oldKeys, $newKeys);

        foreach ($keysAdded as $key => $hash) {
            $cache->set($hash, $this);
            $this->keys[$key] = $hash;
        }

        foreach ($keysRemoved as $key => $hash) {
            $cache->delete($this, $hash);
            unset($this->keys[$key]);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasValue(): bool
    {
        return $this->writes !== 0;
    }

    /**
     * @return int
     */
    public function getWrites(): int
    {
        return $this->writes;
    }

    /**
     * @return int
     */
    public function getReads(): int
    {
        return $this->reads;
    }

    /**
     * @param string|null $hash
     *
     * @return string|null
     */
    public function getKey(?string $hash = null): ?string
    {
        if ($hash) {
            $key = array_search($hash, $this->keys, true);
            return $key === false ? null : $key;
        }

        if (null === $key = $this->key()) {
            $this->rewind();
            $key = $this->key();
        }


        next($this->keys);

        return $key ?: null;
    }

    /**
     * @return array
     */
    public function getKeys(): array
    {
        return array_flip($this->keys);
    }

    /**
     * @param string $key
     * @param string ...$keys
     *
     * @return string[]|null
     */
    public function &addKeys(string $key, ...$keys): ?array
    {
        [$foreignKeys] = $this->validateKeys($key, ...$keys);

        if ($foreignKeys === null) {
            return $foreignKeys;
        }

        $cache = $this->getCache();

        foreach ($foreignKeys as &$foreignKey) {
            if ($foreignKey === null || !$cache->isKeyIncluded($foreignKey, $hash, __METHOD__, [1 => '$key'])) {
                continue;
            }

            $cacheTo = $cache->get($this, $hash);

            if ($cacheTo instanceof self) {
                $keys = $cacheTo->getKeys();
                $failed = $cacheTo->replaceKey($this, ...$foreignKeys);

                if (null === $failed) {
                    // apparently, all keys could be replaced already

                    // hence add keys to the list
                    $this->keys += $keys;

                    $foreignKeys = null;

                    return $foreignKeys;
                }

                // otherwise, find those keys, that have been added (not failed) ...
                foreach (array_diff_key($keys, $cacheTo->getKeys()) as $key => $hash) {
                    // and then add them to the list
                    $this->keys[$key] = $hash;

                    $index = array_search($key, $foreignKey, true);

                    if ($index === false) {
                        $index = array_search($hash, $foreignKey, true);
                    }

                    if ($index !== false) {
                        $foreignKeys[$index] = null;
                    }
                }

                continue;
            }

            // add to cache
            $cache->set($hash, $this);

            // add key
            $this->keys[$key] = $hash;

            $foreignKey = null;
        }
        unset($foreignKey);

        $foreignKeys = array_filter($foreignKeys) ?: null;

        return $foreignKeys;
    }

    /**
     * @param string $key
     * @param string ...$keys
     *
     * @return string[]|null
     */
    public function &removeKeys(string $key, ...$keys): ?array
    {
        return $this->replaceKey(null, $key, ...$keys);
    }

    /**
     * @param RuntimeCacheItem|null $cacheItem
     * @param string $key
     * @param string ...$keys
     *
     * @return string[]|null
     */
    public function &replaceKey(?self $cacheItem, string $key, ...$keys): ?array
    {
        if (is_array($keys[0] ?? null)) {
            [$foreignKeys, $keys] = [null, $keys[0]];
        } else {
            [$foreignKeys, $keys] = $this->validateKeys($key, ...$keys);

            if (0 === count($keys)) {
                return $foreignKeys;
            }
        }

        $oldState = $this->state;
        $this->state = self::STATE_VALUE_DELETING;

        $cache = $this->getCache();

        foreach ($keys as $hash) {
            if ($cacheItem) {
                $cache->set($hash, $cacheItem);
            } else {
                $cache->delete($this, $hash);
            }
        }

        $this->keys = array_diff_key($this->keys, $keys);

        if (0 === count($this->keys)) {
            $this->delete();
        } else {
            $this->state = $oldState;
        }

        return $foreignKeys;
    }


    /**
     * @param string $key
     * @param string ...$keys
     *
     * @return array = [?array $foreignKeys, ?array $ownKeys]
     */
    public function validateKeys(string $key, ...$keys): array
    {
        $keys[] = $key;
        $ownKeys = [];

        foreach ($keys as &$key) {
            if (empty($key)) {
                $key = null;
                continue;
            }

            $hash = $this->keys[$key] ?? null;

            if ($hash) {
                $ownKeys[$key] = $hash;
                $key = null;
                continue;
            }

            $realKEy = array_search($key, $this->keys, true);

            if ($realKEy) {
                $ownKeys[$realKEy] = $key;
                $key = null;
            }
        }

        return [array_filter($keys) ?: null, $ownKeys ?: null];
    }

    /**
     * @param string $key
     *
     * @return string|null
     */
    public function getHash(string $key): ?string
    {
        return $this->keys[$key] ?? null;
    }

    /**
     * @return array
     */
    public function getHashes(): array
    {
        return $this->keys;
    }

    public function isNew(?int $offsetMilliseconds = null): bool
    {
        $offsetMilliseconds ??= $this->getCache()->getNewOffset();

        return $this->created > microtime(true) * 1000 + $offsetMilliseconds;
    }

    public function isState(int $state, bool $strict = false): bool
    {
        if ($strict) {
            return $this->state === $state;
        }

        return $state >= self::STATE_VALUE_DELETING
            ? $this->state >= $state
            : $this->state <= $state && $this->state < self::STATE_VALUE_DELETING;
    }

    public function isInitialized(bool $strict = false): bool
    {
        return $this->isState(self::STATE_INITIALIZED, $strict);
    }

    public function isDeleting(bool $strict = false): bool
    {
        return $this->isState(self::STATE_VALUE_DELETING, $strict);
    }

    public function isDeleted(bool $strict = false): bool
    {
        return $this->isState(self::STATE_VALUE_DELETED, $strict);
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->keys);
    }

    public function offsetGet($offset)
    {
        return $this->keys[$offset];
    }

    public function offsetSet($offset, $value)
    {
        // read-only
    }

    public function offsetUnset($offset)
    {
        // read-only
    }

    public function rewind()
    {
        return reset($this->keys);
    }

    public function current()
    {
        // since all values are strings, let's return the first element in case current() === false to be compatible with an array
        // @see https://www.php.net/manual/en/class.iterator.php#124513
        // If values weren't only non-empty strings, the following code would be required instead:
        // return key($this->keys) !== null ? current($this->keys) : reset($this->keys);
        return current($this->keys) ?: reset($this->keys);
    }

    public function key()
    {
        return key($this->keys);
    }

    public function next()
    {
        return next($this->keys);
    }

    public function valid(): bool
    {
        return key($this->keys) !== null;
    }

    public function count(): int
    {
        return count($this->keys);
    }
}
