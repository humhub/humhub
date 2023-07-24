<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2017-2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\events;

use ArrayAccess;
use Closure;
use Exception;
use humhub\exceptions\ArrayValueFound;
use humhub\exceptions\InvalidArgumentTypeException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use Stringable;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\base\InvalidCallException;
use yii\helpers\ArrayHelper;

/**
 * Event is the base class for all event classes.
 *
 * @since 1.16
 *
 * @property mixed $value
 * @property-write bool $fixed
 * @property-read bool $isFixed
 * @property-write bool $immutable
 * @property-read bool $isImmutable
 * @property mixed $result Alias for $value
 */
class EventWithTypedResult extends Event
{
    protected bool $nullable = true;
    protected bool $immutable = false;
    protected bool $typeFixed = false;
    protected ?array $allowedTypes = null;
    protected ?array $typeDescription = null;

    /**
     * @return array|null
     * @noinspection PhpUnused
     */
    public function getAllowedTypes(): array
    {
        if ($this->typeDescription !== null) {
            return $this->typeDescription;
        }

        if ($this->allowedTypes === null) {
            return $this->typeDescription = ['any'];
        }

        return $this->typeDescription = array_map([$this, 'getTypeDescription'], $this->allowedTypes);
    }


    /**
     * @param null|array|string|callable $allowedTypes
     *
     * @return EventWithTypedResult
     */
    public function setAllowedTypes(?array $allowedTypes): EventWithTypedResult
    {
        $this->checkFixed(__METHOD__);

        $this->allowedTypes = null;
        $this->typeDescription = null;

        if ($allowedTypes === null) {
            return $this;
        }

        foreach ($allowedTypes as $type => $options) {
            if (is_int($type)) {
                $type = $options;
                $options = null;
            }
            $this->addAllowedType($type, $options);
        }
        return $this;
    }

    /**
     * @return bool
     * @noinspection PhpUnused
     */
    public function getIsFixed(): bool
    {
        return $this->typeFixed;
    }

    /**
     * @return bool
     * @noinspection PhpUnused
     */
    public function getIsImmutable(): bool
    {
        return $this->immutable;
    }

    /**
     * @return bool
     * @noinspection PhpUnused
     */
    public function getNullable(): bool
    {
        return $this->nullable;
    }


    /**
     * @param bool $nullable
     *
     * @return EventWithTypedResult
     * @noinspection PhpUnused
     */
    public function setNullable(bool $nullable): EventWithTypedResult
    {
        $this->checkFixed(__METHOD__);

        $this->nullable = $nullable;

        return $this;
    }

    /**
     * @param null|array|string|object|callable $type
     * @param null $options
     *
     * @return string
     * @throws ReflectionException
     */
    public function getTypeDescription($type, $options = null): string
    {
        if (is_int($type)) {
            $type = $options;
            $options = null;
        }

        if (is_callable($type)) {
            if ($type instanceof Closure) {
                /** @noinspection PhpUnhandledExceptionInspection */
                $refFunction = new ReflectionFunction($type);

                return $refFunction->getName();
            }

            [$class, $method] = $type;

            if (is_object($class)) {
                return get_class($class) . "->$method";
            }

            return "$class::$method";
        }

        if (is_object($type)) {
            return sprintf("Object %s of type %s", spl_object_id($type), get_class($type));
        }

        switch ($type) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'int':
                $type = 'integer';
            case 'integer':
            case 'string':
            case 'float':
            case 'null':
            case 'array':
            case 'object':
                if ($options) {
                    return "strict $type";
                }

                return $type;
        }

        if ($options) {
            return "instance of $type";
        }

        return "instance of or class name $type";
    }


    /**
     * @param bool $fixed
     *
     * @return EventWithTypedResult
     */
    public function setFixed(bool $fixed): EventWithTypedResult
    {
        $this->typeFixed = $fixed || $this->checkFixed(__METHOD__);

        return $this;
    }


    /**
     * @param bool $immutable
     *
     * @return EventWithTypedResult
     */
    public function setImmutable(bool $immutable): EventWithTypedResult
    {
        $this->immutable = $immutable || $this->checkImmutable('immutable');

        return $this;
    }


    /**
     * @param mixed $value
     *
     * @return EventWithTypedResult
     */
    public function setValue($value): EventWithTypedResult
    {
        $this->checkImmutable('value');

        if (!$this->nullable || $this->allowedTypes !== null) {
            $this->validate($value);
        }

        $this->value = $value;

        return $this;
    }


    /**
     * @param null|array|string|callable $type
     * @param null $options
     *
     * @return EventWithTypedResult
     */
    public function addAllowedType($type, $options = null): EventWithTypedResult
    {
        if (is_callable($type)) {
            $this->allowedTypes[] = $type;

            return $this;
        }

        //        if (is_array($type))
        //        {
        //            static::_validateByType(
        //                $type,
        //                $value,
        //                $valueIfInvalid,
        //                $self
        //            );
        //
        //            return false;
        //        }

        if (is_object($type)) {
            $this->allowedTypes[] = $type;

            return $this;
        }

        switch (strtolower($type)) {
            case 'int':
            case 'integer':
            case 'string':
            case 'float':
            case 'array':
            case 'object':
            case 'null':
                if ($options === null) {
                    $this->allowedTypes[] = strtolower($type);
                } else {
                    $this->allowedTypes[strtolower($type)] = $options;
                }

                return $this;
        }

        if (class_exists($type) || interface_exists($type) || trait_exists($type, true)) {
            if ($options === null) {
                $this->allowedTypes[] = $type;
            } else {
                $this->allowedTypes[$type] = $options;
            }

            return $this;
        }

        throw new InvalidArgumentException(
            sprintf(
                "Type must be one of int|integer|string|float|array|object|null, a class/interface/trait name, a callable or an object instance. %s given: %s",
                get_debug_type($type),
                serialize($type)
            )
        );
    }

    /**
     * @param string $method
     *
     * @return bool
     */
    protected function checkFixed(string $method): bool
    {
        if ($this->typeFixed) {
            throw new InvalidCallException(sprintf('Settings have been fixed when trying to set %s', $method));
        }

        return $this->typeFixed;
    }


    /**
     * @param string $property
     *
     * @return bool
     */
    protected function checkImmutable(string $property): bool
    {
        if ($this->immutable) {
            throw new InvalidCallException(
                'Object is in read-only mode while trying to set property: ' . get_class($this) . '::' . $property
            );
        }

        return $this->immutable;
    }

    public function fire(string $name, Component $component): EventWithTypedResult
    {
        $component->trigger($name, $this->fix());

        return $this;
    }

    public function fix(): EventWithTypedResult
    {
        $this->setFixed(true);

        return $this;
    }

    public function validate($value, $valueIfInvalid = InvalidArgumentTypeException::class)
    {
        if ($value === null && !$this->nullable) {
            if ($valueIfInvalid === InvalidArgumentTypeException::class) {
                throw new InvalidArgumentTypeException(__METHOD__, [1 => 'value'], $this->allowedTypes, $value, false);
            }

            return $valueIfInvalid;
        }

        if ($this->allowedTypes === null) {
            return $value;
        }

        try {
            static::_validateByType(
                $this->allowedTypes,
                $value,
                $valueIfInvalid,
                $this
            );
        } catch (ArrayValueFound $found) {
            return $found->value;
        }

        return $value;
    }


    /**
     * @param array $types
     * @param mixed $value
     * @param mixed|InvalidArgumentTypeException $valueIfInvalid
     * @param EventWithTypedResult $self
     *
     * @return void
     * @throws ArrayValueFound
     */
    protected static function _validateByType(array $types, &$value, &$valueIfInvalid, EventWithTypedResult $self)
    {
        foreach ($types as $type => $options) {
            if (is_int($type)) {
                $type = $options;
                $options = null;
            }

            if (is_callable($type)) {
                $value = $type($value, $valueIfInvalid, $self);

                if ($value === null && !$self->nullable) {
                    if ($valueIfInvalid === InvalidArgumentTypeException::class) {
                        throw new InvalidArgumentTypeException(
                            __METHOD__,
                            [1 => 'value'],
                            $self->allowedTypes,
                            $value,
                            false
                        );
                    }

                    throw new ArrayValueFound($valueIfInvalid);
                }

                throw new ArrayValueFound($value);
            }

            if (is_array($type)) {
                static::_validateByType(
                    $type,
                    $value,
                    $valueIfInvalid,
                    $self
                );

                continue;
            }

            if (is_object($type)) {
                if ($value === $type) {
                    throw new ArrayValueFound($value);
                }

                continue;
            }

            switch (strtolower($type)) {
                case 'int':
                case 'integer':
                    static::validateInt($value, $options);
                    break;

                case 'string':
                    static::validateString($value, $options);
                    break;

                case 'float':
                    static::validateFloat($value, $options);
                    break;

                case 'array':
                    static::validateArray($value, $options);
                    break;

                case 'object':
                    static::validateObject($value, $options);
                    break;

                case 'null':
                    static::validateNull($value, $options);
                    break;

                default:
                    static::validateClass($type, $value, $options);
            }
        }
    }

    /**
     * @throws ArrayValueFound
     */
    protected static function validateArray(&$value, &$options, ?bool $strict = null, ?bool $toArray = null, ?bool $recursive = null, ?array &$properties = null, bool $skipScalar = false): bool
    {
        $strict ??= $options === 'strict' || $options === true || (($options = (array)$options) && ($options['strict'] ?? in_array('strict', $options, true)));

        // check if strict
        if ($strict && !is_array($value)) {
            return $skipScalar && !is_object($value);
        }

        if (!$strict && is_array($value)) {
            throw new ArrayValueFound($value);
        }

        $options = (array)$options;
        $toArray ??= (bool)($options['array'] ?? in_array('array', $options, true));
        $recursive ??= $toArray && ($options['recursive'] ?? in_array('recursive', $options, true));
        $properties ??= $options['properties'] ?? [];

        if (is_array($value)) {
            $isValid = array_reduce(
                $value,
                static fn($isValid, $item): bool => $isValid && static::validateArray($item, $options, $strict, $toArray, $recursive, $properties, true),
                true
            );

            if ($isValid) {
                throw new ArrayValueFound($value);
            }

            return false;
        }

        if ($value instanceof ArrayAccess) {
            throw new ArrayValueFound($value);
        }

        if (!$toArray) {
            return false;
        }

        $value = ArrayHelper::toArray($value, $properties, $recursive);
        throw new ArrayValueFound($value);
    }

    /**
     * @throws ArrayValueFound
     * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection
     */
    protected static function validateClass(string $type, &$value, &$options): bool
    {
        if (
            !(($isClass = class_exists($type)) || ($isInterface = interface_exists($type)) || $isTrait = trait_exists(
                $type,
                true
            ))
        ) {
            return false;
        }

        if ($options && !is_object($value)) {
            return false;
        }

        if ($isClass) {
            if (is_a($value, $type, true)) {
                throw new ArrayValueFound($value);
            }

            return false;
        }

        if ($isInterface) {
            // check interface
            if (is_subclass_of($value, $type)) {
                throw new ArrayValueFound($value);
            }

            return false;
        }

        if ($isTrait) {
            try {
                $reflection = new ReflectionClass($value);
            } catch (Exception $e) {
                return false;
            }

            $usedTraits = $reflection->getTraitNames();

            if (in_array($type, $usedTraits, true)) {
                throw new ArrayValueFound($value);
            }

            return false;
        }

        return false;
    }

    /**
     * @throws ArrayValueFound
     * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection
     */
    protected static function validateFloat(&$value, &$options): bool
    {
        // check if strict
        if (($options && !is_float($value)) || is_array($value) || is_object($value)) {
            return false;
        }

        if (null !== $float = filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE)) {
            throw new ArrayValueFound($float);
        }

        return false;
    }

    /**
     * @throws ArrayValueFound
     * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection
     */
    protected static function validateInt(&$value, &$options): bool
    {
        // check if strict
        if (($options && !is_int($value)) || is_array($value) || is_object($value)) {
            return false;
        }

        if (null !== $int = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE)) {
            throw new ArrayValueFound($int);
        }

        return false;
    }

    /**
     * @throws ArrayValueFound
     */
    protected static function validateNull(&$value, &$options): bool
    {
        $strict = $options === 'strict' || $options === true || ($options = (array)$options && $options['strict'] ?? in_array(
            'strict',
            $options,
            true
        ));

        // check if strict
        if ($strict && $value !== null) {
            return false;
        }

        if (!empty($value)) {
            $value = null;
            throw new ArrayValueFound($value);
        }

        return false;
    }

    /**
     * @throws ArrayValueFound
     */
    protected static function validateObject(&$value, &$options, ?bool $strict = null, ?bool $toObject = null, ?bool $recursive = null, ?array &$properties = null, bool $skipScalar = false): bool
    {
        $strict ??= $options === 'strict' || $options === true || ($options = (array)$options && $options['strict'] ?? in_array(
            'strict',
            $options,
            true
        ));

        // check if strict
        if ($strict && !is_object($value)) {
            return $skipScalar && !is_array($value);
        }

        if (!$strict && is_object($value)) {
            throw new ArrayValueFound($value);
        }

        $options = (array)$options;
        $toObject ??= (bool)($options['object'] ?? in_array('object', $options, true));
        $recursive ??= $toObject && ($options['recursive'] ?? in_array('recursive', $options, true));

        if (!is_object($value)) {
            $value = (object)(array)$value;
        }

        foreach ($value as $key => $item) {
            try {
                if (!static::validateObject($item, $options, $strict, $toObject, $recursive, $properties, true)) {
                    return false;
                }
            } catch (ArrayValueFound $found) {
                if ($found !== $item) {
                    $value->$key = $found;
                }
            }
        }

        throw new ArrayValueFound($value);
    }

    /**
     * @throws ArrayValueFound
     * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection
     */
    protected static function validateString(&$value, &$options): bool
    {
        // check if strict
        if (($options && !is_string($value)) || is_array($value)) {
            return false;
        }

        if (is_object($value)) {
            if (!$value instanceof Stringable || method_exists($value, '__toString')) {
                return false;
            }

            $value = $value->__toString();

            throw new ArrayValueFound($value);
        }

        $value = (string)$value;

        throw new ArrayValueFound($value);
    }
}
