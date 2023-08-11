<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\libs;

use ArrayIterator;
use humhub\exceptions\InvalidArgumentTypeException;
use humhub\interfaces\ArrayLikeInterface;
use humhub\interfaces\RuntimeCacheStorageInterface;
use Throwable;
use yii\base\InvalidArgumentException;
use Zend\Stdlib\ArrayObject as ZendArrayObject;

/**
 * @since 1.15
 */
class ArrayObject extends ZendArrayObject implements RuntimeCacheStorageInterface
{
    public function __construct($input = [], $flags = ZendArrayObject::STD_PROP_LIST, $iteratorClass = ArrayIterator::class)
    {
        if (!is_array($input)) {
            if (!is_object($input)) {
                throw new InvalidArgumentTypeException(__METHOD__, [1 => '$input'], ['array', 'object'], $input);
            }

            $input = get_object_vars($input);
        }

        parent::__construct($input, $flags, $iteratorClass);
    }

    public function __isset($key)
    {
        if ($this->flag === self::ARRAY_AS_PROPS) {
            return $this->offsetExists($key);
        }

        /** @noinspection InArrayMissUseInspection */
        if (in_array($key, $this->protectedProperties, true)) {
            throw new InvalidArgumentException('$key is a protected property, use a different key');
        }

        return property_exists($this, $key);
    }

    public function append($value): ArrayLikeInterface
    {
        parent::append($value);

        return $this;
    }

    /**
     * @param int|string|null $column_key
     * @param int|string|null $index_key
     *
     * @return array
     */
    public function &column($column_key, $index_key = null): array
    {
        $array = [];

        foreach ($this->getIterator() as $index => $item) {
            try {
                $index = $index_key === null ? $index : $item->{"get$index_key"}();
            } catch (Throwable $t) {
                $index = $item[$index];
            }

            try {
                $array[$index] = $column_key === null ? $item :  $item->{"get$column_key"}();
            } catch (Throwable $t) {
                $array[$index] = $item[$column_key];
            }
        }

        return $array;
    }

    public function keys(): array
    {
        return array_keys($this->storage);
    }


    public function reset(): ArrayLikeInterface
    {
        $this->exchangeArray([]);

        return $this;
    }

    /**
     * Returns whether the requested key exists
     *
     * @param mixed $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return array_key_exists($key, $this->storage);
    }
}
