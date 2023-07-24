<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\interfaces;

use exceptions\ArrayIndexNotFound;
use humhub\exceptions\InvalidArgumentTypeException;
use yii\caching\Cache;
use yii\caching\CacheInterface;
use yii\db\ActiveRecordInterface;

/**
 * @property array|null $exclude
 * @property array|null $include
 * @property int|null $softLimit
 * @property int|null $newItems
 * @property int $newOffset
 * @property-read array $all
 * @property-read int|null $hardLimit
 * @property-read int $distinctCount
 * @property-read int $pointOfTime
 *
 * @since 1.15
 */
interface RuntimeCacheInterface extends CacheInterface
{
    public const FIND_MODE_AUTO = 0;
    public const FIND_MODE_CALLBACK = 1;
    public const FIND_MODE_REGEX = 2;
    public const FIND_MODE_INSTANCE = 3;
    public const FIND_MODE_CLASS = 4;
    public const FIND_MODE_KEY_START = 5;
    public const FIND_MODE_KEY_START_DELIMITED = 6;
    public const FIND_MODE_KEY_SUBSTRING = 7;
    public const FIND_MODE_KEY_SUBSTRING_DELIMITED = 8;
    public const FIND_MODE_IDENTIFIER = 9;

    public const FIND_MODES = [
        self::FIND_MODE_AUTO  => 'FIND_MODE_AUTO',
        self::FIND_MODE_CALLBACK  => 'FIND_MODE_CALLBACK',
        self::FIND_MODE_REGEX  => 'FIND_MODE_REGEX',
        self::FIND_MODE_INSTANCE  => 'FIND_MODE_INSTANCE',
        self::FIND_MODE_CLASS  => 'FIND_MODE_CLASS',
        self::FIND_MODE_KEY_START  => 'FIND_MODE_KEY_START',
        self::FIND_MODE_KEY_START_DELIMITED  => 'FIND_MODE_KEY_START_DELIMITED',
        self::FIND_MODE_KEY_SUBSTRING  => 'FIND_MODE_KEY_SUBSTRING',
        self::FIND_MODE_KEY_SUBSTRING_DELIMITED  => 'FIND_MODE_KEY_SUBSTRING_DELIMITED',
        self::FIND_MODE_IDENTIFIER  => 'FIND_MODE_IDENTIFIER',
    ];

    /**
     * @param string|null $key
     *
     * @return bool|null True or false if $key is included or excluded, respectively, or null if $key was null
     * @throws InvalidArgumentTypeException
     * @noinspection PhpMissingParamTypeInspection
     */
    public function isKeyIncluded($key, ?string &$hash = null): ?bool;

    /**
     * @inheritdoc
     *
     * @param string|null $key
     *
     * @return string|null Hash or null for skipped entries
     * @throws InvalidArgumentTypeException
     */
    public function buildKey($key): ?string;

    /**
     * @param $keyFrom
     * @param string $keyTo
     * @param string ...$to
     *
     * @return array|null Array of failed target keys, or null on success.
     */
    public function link($keyFrom, string $keyTo, string ...$to): ?array;

    /**
     * @param $linkKey
     * @param string ...$linkKeys
     *
     * @return array
     */
    public function &unlink($linkKey, string ...$linkKeys): array;

    /**
     * @param string|object $key
     * @param mixed|callable $ifNotFound If $key is not found, return $ifNotFound unless the latter is callable, in which
     *      case it will be executed with the index as it's first argument
     *
     * @return mixed|ArrayIndexNotFound
     * @throw ArrayIndexNotFound
     */
    public function delete($key, $ifNotFound = [ArrayIndexNotFound::class, 'create']);

    public function getAll(): array;

    /**
     * @return array|null
     */
    public function getExclude(): ?array;

    /**
     * @param string[]|string|null $exclude Regex pattern(s) to match key for exclusion. If both includes and excludes are set, the longer match of the first sub-pattern - or the entire string if no sub-pattern given - will win.
     *
     * @return static
     *
     * @see self::setInclude
     */
    public function setExclude($exclude): self;

    /**
     * @param string $exclusion
     *
     * @return static
     */
    public function addExclusion(string $exclusion): self;

    /**
     * @param string $exclusion
     *
     * @return static
     */
    public function removeExclusion(string $exclusion): self;

    /**
     * @return array|null
     */
    public function getInclude(): ?array;

    /**
     * @param string[]|string|null $include Regex pattern(s) to match key for exclusion. If both includes and excludes are set, the longer match of the first sub-pattern - or the entire string if no sub-pattern given - will win.
     *
     * @return static
     *
     * @see self::setInclude
     */
    public function setInclude($include): self;

    /**
     * @param string $inclusion
     *
     * @return static
     */
    public function addInclusion(string $inclusion): self;

    /**
     * @param string $inclusion
     *
     * @return static
     */
    public function removeInclusion(string $inclusion): self;

    /**
     * @return int
     */
    public function getDistinctCount(): int;

    /**
     * @return int
     */
    public function getSoftLimit(): ?int;

    /**
     * @param int|null $softLimit
     *
     * @return static
     */
    public function setSoftLimit(?int $softLimit): self;

    /**
     * @return int|null
     */
    public function getHardLimit(): ?int;

    /**
     * @return int|null
     */
    public function getNewItems(): ?int;

    /**
     * @param int|null $newItems
     *
     * @return static
     */
    public function setNewItems(?int $newItems): self;

    /**
     * @return int
     */
    public function getNewOffset(): int;

    /**
     * @param int $newOffset
     *
     * @return static
     */
    public function setNewOffset(int $newOffset): self;

    /**
     * @return int Number of milliseconds passed since epoc. Mainly for reproducibility in tests.
     */
    public function getPointOfTime(): int;

    /**
     * For compatibility-reasons in order to support the default cache properties.
     * @see Cache::$keyPrefix
     *
     * @return string
     */
    public function getKeyPrefix(): string;

    /**
     * For compatibility-reasons in order to support the default cache properties.
     * @see Cache::$keyPrefix
     *
     * @param string $keyPrefix
     *
     * @return self
     */
    public function setKeyPrefix(string $keyPrefix): self;

    /**
     * For compatibility-reasons in order to support the default cache properties.
     * @see Cache::$serializer
     *
     * @return array|false|null
     */
    public function getSerializer();

    /**
     * For compatibility-reasons in order to support the default cache properties.
     * @see Cache::$serializer
     *
     * @param array|false|null $serializer
     *
     * @return $this
     */
    public function setSerializer($serializer): self;

    /**
     * For compatibility-reasons in order to support the default cache properties.
     * @see Cache::$defaultDuration
     *
     * @return int
     */
    public function getDefaultDuration(): int;

    /**
     * For compatibility-reasons in order to support the default cache properties.
     * @see Cache::$defaultDuration
     *
     * @param int $defaultDuration
     *
     * @return self
     */
    public function setDefaultDuration(int $defaultDuration): self;

    /**
     * Deletes all values from cache observing the given $pattern.
     *
     * @param string|string[]|object|callable|self $pattern Matching pattern to find values that need to be deleted. If the instance of the cache itself is given,
     *      the entire cache is flushed. Otherwise, it can be a single value or an array of values, each of which can be either
     *      - a regex expression (to match the original key name,
     *      - a simple string (which will denote the beginning of the key; after this beginning there must be the end of the string or an underline character)
     *      - a valid class name, in which case ALL instances in the cache that are of or implement this class, will be removed
     *        (to avoid this behaviour, use self::normaliseObjectIdentifier($className) before passing it as an argument)
     * @param int|null $limit
     * @param int|null $count
     * @param int|null $mode
     * @param bool $reapplyRules
     *
     * @return array|null with deleted values
     *
     * @see static::find()
     */
    public function flush($pattern = self::class, ?int $limit = -1, ?int &$count = 0, ?int $mode = self::FIND_MODE_AUTO, bool $reapplyRules = false): ?array;

    /**
     * @return array
     */
    public function &cleanup(): array;

    /**
     * @return static
     */
    public function sort(): self;

    /**
     * @param object|mixed|null $value
     * @param bool $throw
     * @param string|null $method
     * @param array $parameter
     *
     * @return string[]|null Key-Hash value pairs, or null for skipped entries
     */
    public function buildKeysFromValue($value = null, bool $throw = false, ?string $method = null, array $parameter = [1 => '$value']): ?array;

    /**
     * @param string|object $pattern Literal string, regex pattern, class name, object instance, or callback function
     * @param int $limit
     * @param int|null $count
     * @param int|null $mode
     *
     * @return array|null
     *
     * @see static::findByCallback()
     * @see static::findByRegex()
     * @see static::findByInstance()
     * @see static::findByClass()
     */
    public function find($pattern, int $limit = -1, ?int &$count = 0, ?int $mode = self::FIND_MODE_AUTO): ?array;

    /**
     * @param callable $callback Callback with the following signature: `$callback(RuntimeCacheItem $cacheItem, string
     *     $hash): bool;`
     * @param int|null $limit
     * @param int|null $count
     *
     * @return array|null
     */
    public function findByCallback(callable $callback, ?int $limit = -1, ?int &$count = 0): ?array;

    /**
     * @param UniqueIdentifiersInterface|ActiveRecordInterface|object $object
     * @param int|null $limit
     * @param int|null $count
     *
     * @return array|null
     */
    public function findByIdentifier(object $object, ?int $limit = 1, ?int &$count = 0): ?array;

    /**
     * @param string $pattern
     * @param int|null $limit
     * @param int|null $count
     *
     * @return array|null
     */
    public function findByRegex(string $pattern, ?int $limit = -1, ?int &$count = 0): ?array;

    /**
     * @param object $object
     * @param int|null $limit
     * @param int|null $count
     *
     * @return array|null
     */
    public function findByInstance(object $object, ?int $limit = -1, ?int &$count = 0): ?array;

    /**
     * @param string|object $class
     * @param int|null $limit
     * @param int|null $count
     *
     * @return array|null
     */
    public function findByClass($class, ?int $limit = -1, ?int &$count = 0): ?array;
}
