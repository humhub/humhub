<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use humhub\components\FindInstanceTrait;
use humhub\interfaces\FindInstanceInterface;
use yii\caching\ArrayCache;

use function PHPUnit\Framework\callback;

class FindInstanceCache extends ArrayCache
{
    public ?string $cacheRead = null;
    public ?string $cacheWritten = null;

    /**
     * @var object|null|false
     */
    public $valueRetrieved = false;

    private $callback;
    public string $lastKey = '';

    public function buildKey($key)
    {
        return $this->lastKey = parent::buildKey($key);
    }

    public function getOrSet($key, $callable, $duration = null, $dependency = null)
    {
        $this->callback = $callable;

        return parent::getOrSet($key, [$this, 'callCallback'], $duration, $dependency);
    }
    public function getValue($key)
    {
        $this->cacheRead = $key;
        return parent::getValue($key);
    }

    protected function setValue($key, $value, $duration)
    {
        $this->cacheWritten = $key;

        return parent::setValue($key, $value, $duration);
    }

    public function callCallback(...$args)
    {
        return $this->valueRetrieved = call_user_func($this->callback, ...$args);
    }

    /**
     * @return bool
     */
    public function flushValues(): bool
    {
        $this->resetState();

        return $this->flush();
    }

    public function resetState()
    {
        $this->callback = null;
        $this->cacheRead = null;
        $this->cacheWritten = null;
        $this->valueRetrieved = false;
    }
}
