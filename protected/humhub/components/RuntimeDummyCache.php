<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\components;

use exceptions\ArrayIndexNotFound;
use humhub\interfaces\RuntimeCacheInterface;
use yii\base\Component;

/**
 * @since 1.15
 */
class RuntimeDummyCache extends Component implements RuntimeCacheInterface
{
    /**
     * @inheritdoc
     */
    public function getExclude(): ?array
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function setExclude($exclude): RuntimeDummyCache
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addExclusion(string $exclusion): RuntimeCacheInterface
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeExclusion($exclusion): RuntimeCacheInterface
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getInclude(): ?array
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function setInclude($include): RuntimeDummyCache
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addInclusion(string $inclusion): RuntimeCacheInterface
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeInclusion($inclusion): RuntimeCacheInterface
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function buildKey($key): ?string
    {
        return $key ?: null;
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        return false;
    }

    public function getAll(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function multiGet($keys): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value, $duration = null, $dependency = null): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function multiSet($items, $duration = null, $dependency = null): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function multiAdd($items, $duration = 0, $dependency = null): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function add($key, $value, $duration = 0, $dependency = null): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function exists($key): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function flush($pattern = null, ?int $limit = -1, ?int &$count = 0, ?int $mode = self::FIND_MODE_AUTO, bool $reapplyRules = false): ?array
    {
        return [];
    }

    public function link($keyFrom, string $keyTo, string ...$to): ?array
    {
        return null;
    }


    public function &unlink($linkKey, string ...$linkKeys): array
    {
        $arr = [];
        return $arr;
    }

    public function delete($key, $ifNotFound = [ArrayIndexNotFound::class, 'create']): bool
    {
        return true;
    }

    public function getOrSet($key, $callable, $duration = null, $dependency = null)
    {
        return $callable($this);
    }

    public function buildKeysFromValue($value = null, bool $throw = false, ?string $method = null, array $parameter = [1 => '$value']): ?array
    {
        return null;
    }

    public function find($pattern, int $limit = -1, ?int &$count = 0, ?int $mode = self::FIND_MODE_AUTO): ?array
    {
        return null;
    }

    public function findByCallback(callable $callback, ?int $limit = -1, ?int &$count = 0): ?array
    {
        return null;
    }

    public function findByIdentifier(object $object, ?int $limit = 1, ?int &$count = 0): ?array
    {
        return null;
    }

    public function findByRegex(string $pattern, ?int $limit = -1, ?int &$count = 0): ?array
    {
        return null;
    }

    public function findByInstance(object $object, ?int $limit = -1, ?int &$count = 0): ?array
    {
        return null;
    }

    public function findByClass($class, ?int $limit = -1, ?int &$count = 0): ?array
    {
        return null;
    }

    public function isKeyIncluded($key, ?string &$hash = null): ?bool
    {
        return false;
    }

    public function getDistinctCount(): int
    {
        return 0;
    }

    public function getSoftLimit(): ?int
    {
        return 0;
    }

    public function setSoftLimit(?int $softLimit): self
    {
        return $this;
    }

    public function getHardLimit(): ?int
    {
        return 0;
    }

    public function getNewItems(): ?int
    {
        return null;
    }

    public function setNewItems(?int $newItems): self
    {
        return $this;
    }

    public function getNewOffset(): int
    {
        return 0;
    }

    public function setNewOffset(int $newOffset): self
    {
        return $this;
    }

    public function getPointOfTime(): int
    {
        return microtime(true) * 1000;
    }

    /**
     * @inheritdoc
     */
    public function getKeyPrefix(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function setKeyPrefix(string $keyPrefix): self
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSerializer()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function setSerializer($serializer): self
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultDuration(): int
    {
        return 0;
    }

    /**
     * @inheritdoc
     */
    public function setDefaultDuration(int $defaultDuration): self
    {
        return $this;
    }

    public function &cleanup(): array
    {
        $result = [];
        return $result;
    }

    public function sort(): self
    {
        return $this;
    }

    public function offsetExists($offset): bool
    {
        return false;
    }

    public function offsetGet($offset)
    {
        return false;
    }

    public function offsetSet($offset, $value)
    {
    }

    public function offsetUnset($offset)
    {
    }

}
