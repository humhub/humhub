<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\libs;

use ArrayAccess;
use ArrayIterator;
use Countable;
use humhub\exceptions\InvalidArgumentException;
use humhub\exceptions\InvalidArgumentTypeException;
use OutOfBoundsException;
use SeekableIterator;
use Serializable;
use Stringable;
use Traversable;
use yii\helpers\ArrayHelper;

class StdClass extends \stdClass implements ArrayAccess, Stringable, SeekableIterator, Countable, Serializable
{
    public function __construct(...$args)
    {
        $config = $this->config(new StdClassConfig($this));

        $this->addValues(...$args);

        $config->loading = false;
    }


    /**
     * @param StdClassConfig|null $config
     * @return StdClassConfig
     */
    protected function &config(?StdClassConfig $config = null): StdClassConfig
    {
        static $ref = [];

        $id = spl_object_id($this);

        if ($config === null) {
            return $ref[$id];
        }

        $ref[$id] = $config;

        return $config;
    }

    public function __get($name)
    {
        if ($name === null) {
            return $this->getDefaultValue();
        }

        $this->checkPropertyName($name, __METHOD__);

        return $this->$name ?? null;
    }


    /**
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function __set($name, $value)
    {
        if ($name === null) {
            return $this->setDefaultValue($value);
        }

        $this->checkPropertyName($name, __METHOD__);

        $this->$name = $value;

        return $this;
    }


    public function __isset($name)
    {
        if ($name === null) {
            return $this->hasDefaultValue();
        }

        return property_exists($this, $name);
    }


    public function __unset($name)
    {
        if ($name === null) {
            $this->setDefaultValue(null);

            return $this;
        }

        $name = $this->checkPropertyName($name, __METHOD__);

        unset($this->$name);

        return $this;
    }


    public function __serialize(): array
    {
        return [
            'data' => get_object_vars($this),
            'config' => (object)[
                'default' => $this->config()->default,
            ],
        ];
    }


    public function __toString()
    {
        return serialize($this);
    }


    public function __unserialize($serialized)
    {
        $config = $this->config();

        if (is_object($serialized)) {
            $c = &$serialized->config;
            $d = &$serialized->data;
        } else {
            $c = &$serialized['config'];
            $d = &$serialized['data'];
        }

        foreach ($c as $key => $value) {
            $config->$key = $value;
        }

        $this->addValues($d);
    }

    /**
     * @return mixed|null
     */
    public function getDefaultValue()
    {
        return $this->config()->default;
    }

    /**
     * @param $value
     *
     * @return static
     */
    public function setDefaultValue($value): self
    {
        $this->config()->default = $value;

        return $this;
    }

    public function hasDefaultValue(): bool
    {
        return $this->config()->default !== null;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator(get_object_vars($this));
    }

    public function addValues(...$args): StdClass
    {
        switch (count($args)) {
            case 0:
                return $this;

            case 1:
                $args = reset($args);

                if ($args === null) {
                    return $this->clear();
                }

                if (is_string($args)) {
                    return $this->unserialize($args);
                }
                break;

            default:
                $args = ArrayHelper::merge(...$args);
        }

        if (!is_iterable($args)) {
            if (is_object($args)) {
                $args = ArrayHelper::toArray($args, [], false);
            } else {
                throw new InvalidArgumentTypeException(__METHOD__, 'Argument(s)', [
                    'array',
                    Traversable::class
                ], $args);
            }
        }

        foreach ($args as $k => $v) {
            $this->$k = $v;
        }

        return $this;
    }


    /**
     * @param $name
     * @param string $method
     * @param string|array $parameter
     *
     * @return void
     */
    protected function checkPropertyName($name, string $method, $parameter = ['1' => '$name']): ?string
    {
        switch (true) {
            case is_string($name):
            case is_int($name):
            case $name instanceof Stringable:
                return $name;

            case $name === null:
                return null;

            case is_bool($name):
                return (int)$name;
        }

        throw new InvalidArgumentTypeException(
            $method,
            ['1' => $parameter],
            ['string', 'int', 'bool', Stringable::class]
        );
    }


    public function count(): int
    {
        return count($this->config()->reflection->getProperties());
    }


    /** @noinspection PhpParamsInspection */
    public function current()
    {
        return current($this);
    }


    /** @noinspection PhpParamsInspection */
    public function key()
    {
        return key($this);
    }


    /** @noinspection PhpParamsInspection */
    public function next()
    {
        next($this);

        return $this;
    }


    public function offsetExists($offset): bool
    {
        return $this->__isset($offset);
    }


    public function offsetGet($offset)
    {
        return $this->__get(
            $this->checkPropertyName($offset, __METHOD__, [1 => '$offset'])
        );
    }


    public function offsetSet($offset, $value)
    {
        return $this->__set(
            $this->checkPropertyName($offset, __METHOD__, [1 => '$offset']),
            $value
        );
    }


    public function offsetUnset($offset)
    {
        return $this->__unset(
            $this->checkPropertyName($offset, __METHOD__, [1 => '$offset'])
        );
    }


    /**
     * @inheritdoc
     * @noinspection PhpParamsInspection
     */
    public function rewind()
    {
        reset($this);

        return $this;
    }


    /** @noinspection PhpParamsInspection */
    public function seek($position)
    {
        if (
            !is_int($int = $position) && null === $int = filter_var(
                $position,
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            )
        ) {
            throw new InvalidArgumentTypeException(__METHOD__, [1 => $position], 'int', $position);
        }

        if ($int < 0) {
            $count = $this->count();
            $int = $count + $int;

            if ($int < 0) {
                throw new OutOfBoundsException("Seek position $int is out of range");
            }
        }

        reset($this);

        while ($int-- > 0) {
            next($this);

            if (null === key($this)) {
                throw new OutOfBoundsException("Seek position $int is out of range");
            }
        }

        return key($this);
    }


    public function serialize(): string
    {
        return serialize($this);
    }


    public function unserialize($serialized)
    {
        if (strpos($serialized, 'O:20:"humhub\\libs\\StdClass"') !== 0) {
            throw new InvalidArgumentException(
                __METHOD__,
                [1 => $serialized],
                "string starting with 'O:20:\"humhub\\libs\\StdClass\"'",
                $serialized
            );
        }

        $this->clear();

        $serialized = substr_replace($serialized, 'O:8:"stdClass"', 0, 27);

        $clone = unserialize($serialized, ['allowed_classes' => [\stdClass::class, self::class, static::class]]);

        $this->__unserialize($clone);

        return $this;
    }


    /** @noinspection PhpParamsInspection */
    public function valid(): bool
    {
        return key($this) !== null;
    }


    public static function create(...$args): self
    {
        return new static(...$args);
    }

    public function clear(): self
    {
        $config = $this->config();

        if ($config->loading) {
            return $this;
        }

        foreach ($config->reflection->getProperties() as $property) {
            unset($this->{$property->getName()});
        }

        $config->default = null;

        return $this;
    }
}
