<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */


namespace humhub\components;

use ErrorException;
use exceptions\ArrayIndexNotFound;
use humhub\exceptions\InvalidArgumentException;
use humhub\exceptions\InvalidArgumentTypeException;
use humhub\interfaces\ArrayLikeInterface;
use humhub\interfaces\RuntimeCacheInterface;
use humhub\interfaces\RuntimeCacheStorageInterface;
use humhub\interfaces\UniqueIdentifiersInterface;
use Yii;
use yii\base\UnknownPropertyException;
use yii\caching\Cache;
use yii\db\ActiveRecordInterface;

/**
 * @since 1.15
 *
 * @noinspection MissingPropertyAnnotationsInspection
 */
abstract class RuntimeBaseCache extends Cache implements RuntimeCacheInterface
{
    protected ?array $include = null;
    protected ?array $exclude = null;
    protected int $softLimit = PHP_INT_MAX;
    protected int $newItems = PHP_INT_MAX;
    protected int $newOffset = 200;

    protected int $distinctCount = 0;


    /**
     * @inheritdoc
     */
    public function getKeyPrefix(): string
    {
        return $this->keyPrefix;
    }

    /**
     * @inheritdoc
     */
    public function setKeyPrefix(string $keyPrefix): self
    {
        $this->keyPrefix = $keyPrefix;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * @inheritdoc
     */
    public function setSerializer($serializer): self
    {
        $this->serializer = $serializer;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultDuration(): int
    {
        return $this->defaultDuration;
    }

    /**
     * @inheritdoc
     */
    public function setDefaultDuration(int $defaultDuration): self
    {
        $this->defaultDuration = $defaultDuration;
        return $this;
    }

    /**
     * @return RuntimeCacheStorageInterface<string, RuntimeCacheItem>
     */
    abstract protected function getCache(): RuntimeCacheStorageInterface;

    /**
     * @return ArrayLikeInterface<string, bool>
     */
    abstract protected function getRulesCache(): ArrayLikeInterface;

    public function __get($name)
    {
        if (strtolower($name) === 'cache') {
            throw new UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name);
        }

        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        if (strtolower($name) === 'cache') {
            throw new UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $name);
        }

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        return parent::__set($name, $value);
    }

    public function __isset($name)
    {
        if (strtolower($name) === 'cache') {
            return false;
        }

        return parent::__isset($name);
    }

    /**
     * @return array|null
     */
    public function getExclude(): ?array
    {
        return $this->exclude;
    }

    /**
     * @param $exclude *
     *
     * @inheritdoc
     */
    public function setExclude($exclude): RuntimeBaseCache
    {
        $this->getRulesCache()->reset();

        if ($exclude === null) {
            $this->exclude = $exclude;
        } else {
            $this->exclude = null;
            foreach ((array)$exclude as $exclusion) {
                $this->addExclusion($exclusion, false);
            }
        }

        $this->cleanup(true);

        return $this;
    }

    public function addExclusion(string $exclusion, bool $doCleanup = true): RuntimeCacheInterface
    {
        $this->checkRegex($exclusion, true, __METHOD__, [1 => '$exclusion']);

        $this->exclude[] = $exclusion;
        $this->exclude = array_unique($this->exclude);

        $doCleanup && $this->cleanup(true);

        return $this;
    }

    public function removeExclusion($exclusion): RuntimeCacheInterface
    {
        return $this->removeInExclusion($this->exclude, $exclusion, __METHOD__, [1 => '$exclusion']);
    }

    /**
     * @return array|null
     */
    public function getInclude(): ?array
    {
        return $this->include;
    }

    /**
     * @inheritdoc
     */
    public function setInclude($include): RuntimeBaseCache
    {
        if ($include === null) {
            $this->include = null;
        } else {
            foreach ((array)$include as $exclusion) {
                $this->addInclusion($exclusion, false);
            }
        }

        $this->cleanup(true);

        return $this;
    }

    public function addInclusion(string $inclusion, bool $doCleanup = true): RuntimeCacheInterface
    {
        $this->checkRegex($inclusion, true, __METHOD__, [1 => '$inclusion']);

        $this->include[] = $inclusion;
        $this->include = array_unique($this->include);

        $doCleanup && $this->cleanup(true);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeInclusion($inclusion): RuntimeCacheInterface
    {
        return $this->removeInExclusion($this->include, $inclusion, __METHOD__, [1 => '$inclusion']);
    }

    /**
     * @inheritdoc
     */
    public function getDistinctCount(): int
    {
        return $this->distinctCount;
    }

    /**
     * @return int
     */
    public function getSoftLimit(): ?int
    {
        return $this->softLimit === PHP_INT_MAX ? null : $this->softLimit;
    }

    /**
     * @inheritdoc
     */
    public function setSoftLimit(?int $softLimit): self
    {
        if (($softLimit ??= PHP_INT_MAX) < 0) {
            $softLimit = PHP_INT_MAX;
        }

        if ($this->softLimit === $softLimit) {
            return $this;
        }

        if ($this->softLimit < $softLimit) {
            $this->softLimit = $softLimit;

            $this->flush(null);
        } else {
            $this->softLimit = $softLimit;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getHardLimit(): int
    {
        if ($this->softLimit === PHP_INT_MAX) {
            return PHP_INT_MAX;
        }

        return ($this->softLimit + $this->newItems > PHP_INT_MAX) ? PHP_INT_MAX : $this->softLimit + $this->newItems;
    }

    /**
     * @inheritdoc
     */
    public function getNewItems(): ?int
    {
        return $this->newItems === PHP_INT_MAX ? null : $this->newItems;
    }

    /**
     * @inheritdoc
     */
    public function setNewItems(?int $newItems): self
    {
        if (($newItems ??= PHP_INT_MAX) < 0) {
            $newItems = PHP_INT_MAX;
        }

        if ($this->newItems === $newItems) {
            return $this;
        }

        if ($this->newItems < $newItems) {
            $this->newItems = $newItems;

            $this->flush(null);
        } else {
            $this->newItems = $newItems;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getNewOffset(): int
    {
        return $this->newOffset;
    }

    /**
     * @param int $newOffset
     *
     * @return RuntimeArrayCache
     */
    public function setNewOffset(int $newOffset): RuntimeBaseCache
    {
        $this->newOffset = $newOffset;
        return $this;
    }

    public function getPointOfTime(): int
    {
        return microtime(true) * 1000;
    }

    /**
     * @inheritdoc
     */
    public function isKeyIncluded(
        $key,
        ?string &$hash = null,
        ?string $method = null,
        array $parameter = [1 => '$key']
    ): ?bool {
        if ($key === null) {
            return null;
        }

        if (!is_string($key)) {
            throw new InvalidArgumentTypeException($method ?? __METHOD__, $parameter, 'string', $key, true);
        }

        $hash = mb_strlen($key, '8bit') > 32 ? md5($key) : $key;

        return $this->keys[$hash] ??= $this->evaluateKeyInclusion($key);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function evaluateKeyInclusion(string $key): bool
    {
        $i = $this->applyPatterns($this->include, $key, $this->include === null ? 1 : 0);
        $e = $this->applyPatterns($this->exclude, $key, $this->exclude === null ? 1 : 0);

        return $i >= $e;
    }

    /**
     * @inheritdoc
     */
    public function buildKey($key, ?string $method = null, array $parameter = [1 => '$key']): ?string
    {
        return $this->isKeyIncluded($key, $hash, $method, $parameter) ? $hash : null;
    }

    /**
     * @param object|mixed|null $value
     * @param bool $throw
     * @param string|null $method
     * @param array $parameter
     *
     * @return string[]|null Key-Hash value pairs, or null for skipped entries
     */
    public function &buildKeysFromValue(
        $value = null,
        bool $throw = false,
        ?string $method = null,
        array $parameter = [1 => '$value']
    ): ?array {
        $keys = [];

        if ($value instanceof UniqueIdentifiersInterface) {
            foreach ($value->getUniqueIDs() as $key) {
                $this->addKeyHash($keys, $key);
            }
        } elseif ($value instanceof ActiveRecordInterface) {
            $this->addKeyHash($keys, self::normaliseObjectIdentifier($value, $value->getPrimaryKey(true)));

            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            $columns = $value::getTableSchema()->getColumnNames();

            foreach (array_intersect($columns, ['id', 'guid']) as $column) {
                $this->addKeyHash($keys, self::normaliseObjectIdentifier($value, $value->$column));
            }
        } elseif ($throw) {
            throw new InvalidArgumentTypeException(
                $method ?? __METHOD__,
                $parameter,
                [ActiveRecordInterface::class, UniqueIdentifiersInterface::class],
                $value,
                false,
                ' or Argument #1 ($key) must not be null'
            );
        }

        if (count($keys) === 0) {
            $keys = null;
        }

        return $keys;
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        $hash = $this->buildKey($key, __METHOD__);

        return $this->getValue($hash ?? $key);
    }

    public function getAll(bool $preserveSorting = false): array
    {
        return $this->getCache()->column('value', 'key');
    }

    /**
     * @inheritdoc
     */
    public function multiGet($keys): array
    {
        $results = array_flip($keys);

        foreach ($keys as $key) {
            $results[$key] = $this->get($key);
        }

        return $results;
    }

    /**
     * @inheritdoc
     *
     * @param string|array|null $key
     */
    public function set($key, $value = null, $duration = null, $dependency = null): bool
    {
        if (!empty($key)) {
            if (is_array($key) && !is_int(key($key))) {
                return count($this->multiSet($key)) === 0;
            }

            $keys = (array)$key;
            $hashes = [];

            foreach ($keys as $key) {
                if ($hash = $this->buildKey($key, __METHOD__)) {
                    $hashes[$key] = $hash;
                }
            }

            // check if some keys are excluded
            if (count($hashes) < count($keys)) {
                return false;
            }
        } elseif (null === $hashes = $this->buildKeysFromValue($value, true, __METHOD__)) {
            return false;
        }

        return $this->setValue($hashes, $value);
    }

    /**
     * @inheritdoc
     */
    public function multiSet($items, $duration = null, $dependency = null): array
    {
        $failedKeys = [];

        foreach ($items as $key => $value) {
            if (!$this->set($key, $value)) {
                $failedKeys[] = $key;
            }
        }

        return $failedKeys;
    }

    /**
     * @param array $items
     * @param int $duration ignored!
     * @param null $dependency ignored!
     *
     * @inheritdoc
     */
    public function multiAdd($items, $duration = 0, $dependency = null): array
    {
        $failedKeys = [];

        foreach ($items as $key => $value) {
            if (!$this->add($key, $value)) {
                $failedKeys[] = $key;
            }
        }

        return $failedKeys;
    }

    public function delete($key, $ifNotFound = [ArrayIndexNotFound::class, 'create'])
    {
        if (is_string($key)) {
            $this->isKeyIncluded($key, $hash, __METHOD__);

            return $this->deleteValue($hash, $key, $ifNotFound);
        }

        return $this->flush($key, null, $count);
    }

    /**
     * @inheritdoc
     *
     * @param bool $returnCacheItem Internal use.
     */
    public function &find(
        $pattern,
        ?int $limit = -1,
        ?int &$count = 0,
        ?int $mode = self::FIND_MODE_AUTO,
        bool $returnCacheItem = false
    ): ?array {
        $check = false;

        while ($mode === self::FIND_MODE_AUTO) {
            $check = true;

            if ($pattern instanceof UniqueIdentifiersInterface || $pattern instanceof ActiveRecordInterface) {
                $mode = self::FIND_MODE_IDENTIFIER;
                break;
            }

            if (is_callable($pattern)) {
                $mode = self::FIND_MODE_CALLBACK;
                break;
            }

            if (is_object($pattern)) {
                $mode = self::FIND_MODE_INSTANCE;
                break;
            }

            if (!is_string($pattern)) {
                throw new InvalidArgumentTypeException(
                    __METHOD__,
                    [1 => '$pattern'],
                    ['string', 'regex', 'class', 'object', 'callable'],
                    $pattern,
                    false,
                    sprintf('if Argument #3 ($mode) = %s::FIND_MODE_AUTO', self::class)
                );
            }

            if ($this->checkRegex($pattern, false)) {
                $mode = self::FIND_MODE_REGEX;
                break;
            }

            if (class_exists($pattern)) {
                $mode = self::FIND_MODE_CLASS;
                break;
            }

            $mode = self::FIND_MODE_KEY_START_DELIMITED;
        }

        switch ($mode) {
            case self::FIND_MODE_CALLBACK:
                if (!$check && !is_callable($pattern)) {
                    throw new InvalidArgumentTypeException(
                        __METHOD__,
                        [1 => '$pattern'],
                        'callable',
                        $pattern,
                        false,
                        sprintf('if Argument #3 ($mode) = %s::%s', self::class, self::FIND_MODES[$mode])
                    );
                }
                return $this->findByCallback($pattern, $limit, $count, $returnCacheItem);

            case self::FIND_MODE_IDENTIFIER:
                if (!$check && !$pattern instanceof UniqueIdentifiersInterface && !$pattern instanceof ActiveRecordInterface) {
                    throw new InvalidArgumentTypeException(
                        __METHOD__,
                        [1 => '$pattern'],
                        [UniqueIdentifiersInterface::class, ActiveRecordInterface::class],
                        $pattern,
                        false,
                        sprintf('if Argument #3 ($mode) = %s::%s', self::class, self::FIND_MODES[$mode])
                    );
                }
                return $this->findByIdentifier($pattern, $limit, $count, $returnCacheItem);

            case self::FIND_MODE_REGEX:
                if (!$check && !$this->checkRegex($pattern, false)) {
                    throw new InvalidArgumentTypeException(
                        __METHOD__,
                        [1 => '$pattern'],
                        'regex',
                        $pattern,
                        false,
                        sprintf('if Argument #3 ($mode) = %s::%s', self::class, self::FIND_MODES[$mode])
                    );
                }
                return $this->findByRegex($pattern, $limit, $count, $returnCacheItem);

            case self::FIND_MODE_INSTANCE:
                if (!$check && !is_object($pattern)) {
                    throw new InvalidArgumentTypeException(
                        __METHOD__,
                        [1 => '$pattern'],
                        'object',
                        $pattern,
                        false,
                        sprintf('if Argument #3 ($mode) = %s::%s', self::class, self::FIND_MODES[$mode])
                    );
                }
                return $this->findByInstance($pattern, $limit, $count, $returnCacheItem);

            case self::FIND_MODE_CLASS:
                if (is_object($pattern)) {
                    $pattern = get_class($pattern);
                    $check = true;
                }

                if (!$check && !class_exists($pattern)) {
                    throw new InvalidArgumentTypeException(
                        __METHOD__,
                        [1 => '$pattern'],
                        'class',
                        $pattern,
                        false,
                        sprintf('if Argument #3 ($mode) = %s::%s', self::class, self::FIND_MODES[$mode])
                    );
                }
                return $this->findByClass($pattern, $limit, $count, $returnCacheItem);

            case self::FIND_MODE_KEY_START:
                $regex = "@^(%s)@";
                break;

            case self::FIND_MODE_KEY_START_DELIMITED:
                $regex = "@^(%s)(?:$|_)@";
                break;

            case self::FIND_MODE_KEY_SUBSTRING:
                $regex = "@(%s)@";
                break;

            case self::FIND_MODE_KEY_SUBSTRING_DELIMITED:
                $regex = "@(?:^|_)(%s)(?:$|_)@";
                break;

            default:
                throw new InvalidArgumentException(
                    __METHOD__,
                    [3 => '$mode'],
                    self::FIND_MODES,
                    $pattern,
                    false,
                    sprintf('if Argument #3 ($mode) = %s::%s', self::class, self::FIND_MODES[self::FIND_MODE_AUTO])
                );
        }

        if (!$check && !is_string($pattern)) {
            throw new InvalidArgumentTypeException(
                __METHOD__,
                [1 => '$pattern'],
                ['string'],
                $pattern,
                false,
                sprintf('if Argument #3 ($mode) = %s::%s', self::class, self::FIND_MODES[$mode])
            );
        }

        $pattern = sprintf($regex, preg_quote($pattern, '@'));

        return $this->findByRegex($pattern, $limit, $count, $returnCacheItem);
    }

    /**
     * @inheritdoc
     */
    public function &findByRegex(
        string $pattern,
        ?int $limit = -1,
        ?int &$count = 0,
        bool $returnCacheItem = false
    ): ?array {
        $this->checkRegex($pattern, true, __METHOD__);

        return $this->findByCallback(
            fn(RuntimeCacheItem $cacheItem, string $hash): bool => null !== preg_filter(
                $pattern,
                '$0',
                $cacheItem->getKey($hash)
            ),
            $limit,
            $count,
            $returnCacheItem
        );
    }

    /**
     * @inheritdoc
     */
    public function &findByInstance(
        object $object,
        ?int $limit = -1,
        ?int &$count = 0,
        bool $returnCacheItem = false
    ): ?array {
        return $this->findByCallback(
            fn(
                RuntimeCacheItem $cacheItem,
                string $hash
            ): bool => $object === $cacheItem->getValue(false),
            $limit,
            $count,
            $returnCacheItem
        );
    }

    /**
     * @inheritdoc
     */
    public function &findByClass($class, ?int $limit = -1, ?int &$count = 0, bool $returnCacheItem = false): ?array
    {
        if (is_object($class)) {
            $class = get_class($class);
        } elseif (!is_string($class)) {
            throw new InvalidArgumentTypeException(__METHOD__, [1 => '$class'], ['class', 'object'], $class);
        } elseif (!class_exists($class)) {
            throw new InvalidArgumentTypeException(
                __METHOD__,
                [1 => '$class'],
                'class',
                $class,
                false,
                sprintf('Invalid class name "%s"', $class)
            );
        }

        return $this->findByCallback(
            fn(
                RuntimeCacheItem $cacheItem,
                string $hash
            ): bool => ($value = $cacheItem->getValue()) && $value instanceof $class,
            $limit,
            $count,
            $returnCacheItem
        );
    }

    abstract protected function &flushValues(?int &$count = 0): ?array;

    /**
     * @inheritdoc
     * @see RuntimeCacheInterface::flushValues()
     */
    public function &flush(
        $pattern = self::class,
        ?int $limit = -1,
        ?int &$count = 0,
        ?int $mode = self::FIND_MODE_AUTO,
        $reapplyRules = false
    ): ?array {
        if ($pattern === null) {
            return $this->cleanup();
        }

        if (($pattern === $this || $pattern === self::class)) {
            if (-1 === $limit ??= -1) {
                return $this->flushValues($count);
            }

            $pattern = '/.*/';
            $mode = self::FIND_MODE_REGEX;
        }

        // make a copy of the cache
        /** @var RuntimeCacheItem[] $items */
        if (null === $items = $this->find($pattern, $limit, $count, $mode, true)) {
            return $items;
        }

        foreach ($items as &$cacheItem) {
            if ($cacheItem->isDeleting()) {
                $cacheItem = $cacheItem->getValue(false);
                continue;
            }

//            if ($reapplyRules) {
//                $i = $this->applyPatterns($this->include, $key, $this->include === null ? 1 : 0);
//                $e = $this->applyPatterns($this->exclude, $key, $this->exclude === null ? 1 : 0);
//
//                $x = $i >= $e;
//
//                /** @noinspection PhpForeachNestedOuterKeyValueVariablesConflictInspection */
//                foreach ($cacheItem as $key => $hash) {
//                    $this->keys[$hash] = $x;
//                }
//
//                if ($x) {
//                    continue;
//                }
//            }

            $cacheItem = $this->deleteValue($cacheItem);
        }

        return $items;
    }

    /**
     * @inheritdoc
     */
    public function sort(): self
    {
        $this->cache->uasort(static function (RuntimeCacheItem $A, RuntimeCacheItem $B) {
            // check for the same object
            if ($A === $B) {
                return 0;
            }

            $a = $A->getReads();
            $b = $B->getReads();

            if ($a > $b) {
                return -1;
            }

            if ($a < $b) {
                return 1;
            }

            $a = $A->getCreated();
            $b = $B->getCreated();

            if ($a > $b) {
                return -1;
            }

            if ($a < $b) {
                return 1;
            }

            return 0;
        });

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function &cleanup(bool $doEvaluateKeyInclusion = false): array
    {
        $this->sort();

        $rulesCache = $this->getRulesCache();

        if ($doEvaluateKeyInclusion) {
            $rulesCache->reset();
        } else {
            $doEvaluateKeyInclusion = 0 === count($rulesCache);
        }

        $items = [];

        if (!$doEvaluateKeyInclusion && $this->softLimit === PHP_INT_MAX) {
            return $items;
        }

        $last = null;
        $distinct = 0;
        $new = 0;


    /**
     * @param RuntimeCacheItem $cacheItem
     * @param array $items
     *
     * @return array
     */
        $delete = static function (RuntimeCacheItem $cacheItem, array &$items): void {
            $value = $cacheItem->getValue(false);

            foreach ($cacheItem->getHashes() as $key => $hash) {
                $items[$key] = $value;
            }

            $cacheItem->delete();
        };

        $cache = $this->getCache();
        foreach ($cache->keys() as $hash) {
            /** @var RuntimeCacheItem $cacheItem */
            if (null === $cacheItem = $cache[$hash] ?? null) {
                continue;
            }
            if ($cacheItem->isDeleting()) {
                $items[$cacheItem->getKey($hash)] = $cacheItem->getValue(false);
                continue;
            }

            if ($last === $cacheItem) {
                continue;
            }

            $last = $cacheItem;

            if ($doEvaluateKeyInclusion) {
                $keep = true;

                foreach ($cacheItem->getHashes() as $key => $hash) {
                    $keep = ($rulesCache[$hash] = $this->evaluateKeyInclusion($key)) && $keep;
                }

                if (!$keep) {
                    $delete($cacheItem, $items);
                    continue;
                }
            }

            if ($distinct > $this->softLimit) {
                if ($cacheItem->isNew($this->newOffset)) {
                    if ($new > ($this->newItems)) {
                        $delete($cacheItem, $items);
                        continue;
                    }
                    ++$distinct;
                    ++$new;
                    continue;
                }

                $delete($cacheItem, $items);
                continue;
            }

            ++$distinct;
        }

        return $items;
    }

    protected function checkRegex(
        $regex,
        bool $throw = true,
        ?string $method = null,
        array $parameter = [1 => '$regex']
    ): bool {
        $e = null;

        if (is_array($regex)) {
            foreach ($regex as $pattern) {
                $e = $this->checkRegex($pattern, $throw, $method, $parameter) && $e;
            }

            return $e;
        }

        if (!is_string($regex)) {
            throw new InvalidArgumentTypeException(
                $method ?? __METHOD__,
                $parameter,
                ['string', 'string[]'],
                $regex,
                '- "string" must be a valid regex pattern (see https://www.php.net/manual/en/pcre.pattern.php).'
            );
        }

        // check if regex is valid
        try {
            if (false === preg_match($regex, '') || preg_last_error()) {
                $e = true;
            }
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ErrorException $e) {
        }

        if ($e) {
            if ($throw) {
                throw new InvalidArgumentException(
                    $method ?? __METHOD__,
                    $parameter,
                    ['string', 'string[]'],
                    $regex,
                    sprintf(
                        '- "string" must be a valid regex pattern (see https://www.php.net/manual/en/pcre.pattern.php). Error: %s',
                        preg_last_error_msg()
                    ),
                    preg_last_error()
                );
            }

            return false;
        }

        //  check if regex includes at least one (1) subpattern
        //        if (!preg_match('@(?<!(?<!\\\\)\\\\)\\((?![?*])@', $regex)) {
        //            throw new InvalidArgumentException("The regex pattern $regex does not include the mandatory subpattern!");
        //        }

        return true;
    }

    /**
     * @param array|null $patterns
     * @param string $key
     * @param int $default
     *
     * @return int
     * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection
     */
    protected function applyPatterns(?array &$patterns, string $key, int $default = 0): int
    {
        if ($patterns === null) {
            return $default;
        }

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $key, $matches)) {
                $match = $matches['match'] ?? $matches[1] ?? $matches[0];
                $len = mb_strlen($match, '8bit');
                if ($len > $default) {
                    $default = $len;
                }
            }
        }

        return $default;
    }

    protected function addKeyHash(array &$keys, $key): bool
    {
        $result = $keys[$key] ?? null;

        if ($result !== null) {
            return $result;
        }

        if (null === $hash = $this->buildKey($key)) {
            return $keys[$key] = false;
        }

        return $keys[$key] = $hash;
    }

    /**
     * @param array|null $store
     * @param string|int $pattern Index or pattern of the inclusion
     * @param string $method
     * @param array $parameter
     *
     * @return RuntimeCacheInterface
     */
    protected function removeInExclusion(
        ?array &$store,
        $pattern,
        string $method,
        array $parameter
    ): RuntimeCacheInterface {
        if (is_int($pattern)) {
            if (null === $store) {
                return $this;
            }

            unset($store[$pattern]);
        } elseif (!is_string($pattern)) {
            throw new InvalidArgumentTypeException($method, $parameter, ['string', 'int'], $pattern);
        } elseif (false !== $index = array_search($pattern, $store, true)) {
            if (null === $store) {
                return $this;
            }

            unset($store[$index]);
        } else {
            return $this;
        }

        if (count($store) === 0) {
            $store = null;
        }

        $this->cleanup(true);

        return $this;
    }

    public static function cacheDeleteByClass($class, $condition)
    {
        $cache = Yii::$app->runtimeCache;

        if (is_array($condition) && $class::isPrimaryKey(array_keys($condition))) {
            $cache->delete(static::normaliseObjectIdentifier($class, $condition));
        } else {
            $cache->flush($class);
        }
    }

    public static function normaliseObjectIdentifier($classOrObject, $id = null, ?string &$idWasUsed = null): string
    {
        if (is_object($classOrObject)) {
            $classOrObject = get_class($classOrObject);
        } elseif (!is_string($classOrObject)) {
            throw new InvalidArgumentTypeException(
                __METHOD__,
                [1 => '$classOrObject'],
                ['object', 'string'],
                $classOrObject
            );
        }

        $idWasUsed = '';
        $id = $id === 0 || !empty($id) ? "__" . ($idWasUsed = implode('_', (array)$id)) : '';

        return str_replace('\\', '_', ltrim($classOrObject, '\\')) . $id;
    }
}
