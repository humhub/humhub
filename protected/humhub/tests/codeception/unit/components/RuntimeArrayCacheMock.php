<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use humhub\components\RuntimeArrayCache;
use humhub\interfaces\RuntimeCacheStorageInterface;

/**
 * @since 1.15
 *
 * @noinspection MissingPropertyAnnotationsInspection
 */
class RuntimeArrayCacheMock extends RuntimeArrayCache
{
    public ?array $cacheRead = null;
    public ?array $cacheWritten = null;

    /**
     * @var object|null|false
     */
    public $valueRetrieved = false;

    private $callback;
    public $lastKey = false;

    public ?int $pointOfTime = null;

    public function buildKey($key, string $method = null, array $parameter = [1 => '$key']): ?string
    {
        if ($this->lastKey === false) {
            $this->lastKey = [];
        }

        $this->lastKey[] = $hash = parent::buildKey($key, $method);

        return $hash;
    }

    public function getOrSet($key, $callable, $duration = null, $dependency = null)
    {
        $this->callback = $callable;

        return parent::getOrSet($key, [$this, 'callCallback'], $duration, $dependency);
    }
    public function getValue($key)
    {
        $this->cacheRead[] = $key;
        return parent::getValue($key);
    }

    protected function setValue($hash, $value, $duration = 0): bool
    {
        $this->cacheWritten[] = $hash;

        return parent::setValue($hash, $value, $duration);
    }

    public function getPointOfTime(): int
    {
        return $this->pointOfTime ?? parent::getPointOfTime();
    }

    /**
     * @param int|null $pointOfTime
     *
     * @return RuntimeArrayCacheMock
     */
    public function setPointOfTime(?int $pointOfTime): RuntimeArrayCacheMock
    {
        $this->pointOfTime = $pointOfTime < 0 ? parent::getPointOfTime() : $pointOfTime;

        return $this;
    }

    public function callCallback(...$args)
    {
        return $this->valueRetrieved = call_user_func($this->callback, ...$args);
    }

    public function resetState()
    {
        $this->callback = null;
        $this->cacheRead = null;
        $this->cacheWritten = null;
        $this->valueRetrieved = false;
    }

    public function checkRegex(
        $regex,
        bool $throw = true,
        ?string $method = null,
        array $parameter = [1 => '$regex']
    ): bool {
        return parent::checkRegex($regex, $throw, $method, $parameter);
    }

    public function &getCache(): RuntimeCacheStorageInterface
    {
        return $this->cache;
    }
}
