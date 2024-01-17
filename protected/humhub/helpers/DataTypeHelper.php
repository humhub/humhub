<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\helpers;

use humhub\exceptions\InvalidArgumentClassException;
use humhub\exceptions\InvalidArgumentTypeException;
use humhub\exceptions\InvalidArgumentValueException;
use Stringable;

/**
 * @since 1.16
 */
class DataTypeHelper
{
    public const CLASS_CHECK_VALUE_IS_NULL = 128;
    public const CLASS_CHECK_TYPE_NOT_IN_LIST = 32;
    public const CLASS_CHECK_INVALID_TYPE_PARAMETER = 2;
    public const CLASS_CHECK_VALUE_IS_EMPTY = 4;
    public const CLASS_CHECK_INVALID_CLASSNAME_PARAMETER = 1;
    public const CLASS_CHECK_INVALID_TYPE = 8;
    public const CLASS_CHECK_NON_EXISTING_CLASS = 16;
    public const CLASS_CHECK_VALUE_IS_INSTANCE = 64;


    /**
     * @param mixed $input Variable to be checked.
     * @param string[] $types Allowed types. Valid input are
     * ``
     * - simple type names as returned by gettype()
     * - `null` value or `'NULL'` string
     * - class, interface, or trait names
     * - class instances whose class type will be checked
     * - `callable`, e.g. `is_scalar`
     * ``
     * @param string $parameterOrMessage = Name of the argument to be used for InvalidArgumentTypeException
     *
     * @return void
     * @since 1.16
     * @see self::checkType()
     * @see InvalidArgumentTypeException
     * @throws InvalidArgumentTypeException
     */
    public static function ensureType($input, array $types, string $parameterOrMessage = '$input'): void
    {
        if (self::checkType($input, $types) !== null) {
            return;
        }

        $validTypes = array_map(
            static fn($item) => is_callable($item, true, $name) || (is_object($item) && $name = get_class($item))
                ? $name
                : $item,
            $types
        );

        throw new InvalidArgumentTypeException(
            $parameterOrMessage,
            $validTypes,
            $input
        );
    }

    /**
     * @param mixed $input Variable to be checked.
     * @param string[] $types Allowed types. Valid input are
     * ``
     * - simple type names as returned by gettype()
     * - `null` value or `'NULL'` string
     * - class, interface, or trait names
     * - class instances whose class type will be checked
     * - `callable`, e.g. `is_scalar`
     * ``
     * @param bool $returnIndex if set to `true`, the method will return the types' index, rather that it's name.
     *
     * @return string|null
     * @since 1.16
     * @see gettype
     */
    public static function checkType($input, array $types, bool $returnIndex = false): ?string
    {

        // if the last item in `$types` is the value `true`, return the $types´ index, rather its value
        if (true === ($types[array_key_last($types)] ?? false)) {
            $returnIndex = true;
            array_pop($types);
        }

        $types = self::parseTypes($types);

        if ($input === null) {
            if (in_array($i = null, $types, true) || in_array($i = 'NULL', $types, true)) {
                if ($returnIndex) {
                    return array_search($i, $types, true);
                }

                return 'NULL';
            }
        } else {
            $inputType = gettype($input);

            foreach ($types as $i => $typeToCheck) {
                if ($typeToCheck === null) {
                    continue;
                }

                $typeToCheck = static::checkTypeHelper($input, $inputType, $typeToCheck);

                if ($typeToCheck !== null) {
                    return $returnIndex
                        ? $i
                        : $typeToCheck;
                }
            }
        }

        return null;
    }

    protected static function checkTypeHelper(&$input, string $inputType, $typeToCheck): ?string
    {

        if (is_string($typeToCheck) || (is_object($typeToCheck) && $typeToCheck = get_class($typeToCheck))) {
            switch ($typeToCheck) {
                case 'boolean':     // the result of gettype()
                case 'bool':        // the name as it is defined in code
                    return $inputType === 'boolean'
                        // return it the way it was tested
                        ? $typeToCheck
                        : null;

                case 'integer':     // the result of gettype()
                case 'int':         // the name as it is defined in code
                    return $inputType === 'integer'
                        // return it the way it was tested
                        ? $typeToCheck
                        : null;

                case 'string':
                case 'array':
                case 'object':
                case 'resource':
                case 'resource (closed)': // as of PHP 7.2.0
                case 'NULL':
                case 'unknown type':
                    return $inputType === $typeToCheck
                        ? $typeToCheck
                        : null;

                case 'double':
                case 'float':
                    return $inputType === 'double'
                        ? $typeToCheck
                        : null;

                case Stringable::class:
                    return $inputType === 'object'
                    && ($input instanceof Stringable || is_callable([$input, '__toString']))
                        ? $typeToCheck
                        : null;

                default:
                    /** @noinspection NotOptimalIfConditionsInspection */
                    if (
                        (class_exists($typeToCheck) || interface_exists($typeToCheck))
                        && $input instanceof $typeToCheck
                    ) {
                        return $typeToCheck;
                    }

                    if (
                        trait_exists($typeToCheck, true)
                        && in_array($typeToCheck, static::classUsesTraits($input), true)
                    ) {
                        return $typeToCheck;
                    }
            }
        }

        if (
            is_callable($typeToCheck, false, $name)
            && $typeToCheck($input)
        ) {
            return $name;
        }

        return null;
    }

    /**
     * @param mixed $value value to be tested or converted
     * @param bool $strict indicates if strict comparison should be performed:
     * ``
     * - if True, `$value` must already be of type `int`.
     * - if False, a conversion to `int` is attempted.
     * ``
     * @param bool $throwException throws an exception instead of returning `null`
     *
     * @since 1.16
     */
    public static function filterBool($value, ?bool $strict = false, bool $throwException = false): ?bool
    {
        $param = $throwException ? '$value' : false;
        if ($strict) {
            $types = ['bool'];
            $input = $value;
        } elseif ($strict === null) {
            try {
                return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool)$value;
            } catch (\Throwable $e) {
                return false;
            }
        } else {
            $types = ['bool', null];
            $input = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        return self::checkType($input, $types, $param) === null ? null : $input;
    }

    /**
     * Checks if the class has this class as one of its parents
     *
     * Code of the thrown Exception is a bit-mask consisting of the following bits
     * - self::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER: Invalid $className parameter
     * - self::CLASS_CHECK_INVALID_TYPE_PARAMETER: Invalid $type parameter
     * - self::CLASS_CHECK_VALUE_IS_EMPTY: Empty parameter
     * - self::CLASS_CHECK_INVALID_TYPE: Invalid type
     * - self::CLASS_CHECK_NON_EXISTING_CLASS: Non-existing class
     * - self::CLASS_CHECK_TYPE_NOT_IN_LIST: Class that is not in $type parameter
     * - self::CLASS_CHECK_VALUE_IS_INSTANCE: $className is an object instance
     * - self::CLASS_CHECK_VALUE_IS_NULL: NULL value
     *
     * @param string|object|null|mixed $className Object or classname to be checked. Null may be valid if included in
     *     $type. Everything else is invalid and either throws an error (default) or returns NULL, if $throw is false.
     * @param string|string[] $types (List of) class, interface or trait names that are allowed.
     *        If NULL is included, NULL values are also allowed.
     * @param bool|null $throwException Determines if an Exception should be thrown if $className doesn't match $type,
     *     or simply return NULL. Invalid $types always throw an error!
     * @param bool $strict If set to true, no invalid characters are removed from a $className string.
     *        If set to false, please make sure you use the function's return value, rather than $className, as they
     *     might diverge
     *
     * @return string|null
     * @throws InvalidArgumentTypeException|InvalidArgumentClassException|InvalidArgumentValueException
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpUnhandledExceptionInspection
     */
    public static function filterClassType($className, $types, bool $throwException = true, ?bool $strict = true): ?string
    {
        if (empty($types)) {
            throw new InvalidArgumentValueException(
                '$type',
                ['string', 'string[]'],
                $types,
                self::CLASS_CHECK_INVALID_TYPE_PARAMETER + self::CLASS_CHECK_VALUE_IS_EMPTY
            );
        }

        $types = static::parseTypes($types);
        $valid = [];
        $allowNull = false;

        // validate the type array
        foreach ($types as $index => &$item) {
            if ($item === null) {
                $allowNull = true;
                continue;
            }

            if (is_object($item)) {
                $valid[get_class($item)] = false;
                continue;
            }

            if (!is_string($item)) {
                throw new InvalidArgumentValueException(
                    sprintf('$type[%s]', $index),
                    ['class', 'object'],
                    $item,
                    self::CLASS_CHECK_INVALID_TYPE_PARAMETER + self::CLASS_CHECK_INVALID_TYPE
                );
            }

            $isTrait = false;

            if (!class_exists($item) && !interface_exists($item, false) && !($isTrait = trait_exists($item, false))) {
                throw new InvalidArgumentValueException(
                    sprintf('$type[%s]', $index),
                    'a valid class/interface/trait name or an object instance',
                    $item,
                    self::CLASS_CHECK_INVALID_TYPE_PARAMETER + self::CLASS_CHECK_NON_EXISTING_CLASS
                );
            }

            $valid[$item] = $isTrait;
        }
        // make sure the reference is not going to be overwritten
        unset($item);

        // save the types for throwing exceptions
        $types = array_keys($valid);
        if ($allowNull) {
            $types[] = null;
        }

        // check for null input
        if ($className === null) {
            // check if null is allowed
            if ($allowNull) {
                return null;
            }

            if (!$throwException) {
                return null;
            }

            throw new InvalidArgumentTypeException(
                '$className',
                $types,
                $className,
                self::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER + self::CLASS_CHECK_VALUE_IS_EMPTY + self::CLASS_CHECK_INVALID_TYPE + self::CLASS_CHECK_VALUE_IS_NULL
            );
        }

        // check for other empty input
        if (empty($className)) {
            if ((!$strict && $allowNull) || !$throwException) {
                return null;
            }

            throw is_string($className)
                ? new InvalidArgumentClassException(
                    '$className',
                    $types,
                    $className,
                    self::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER + self::CLASS_CHECK_VALUE_IS_EMPTY + self::CLASS_CHECK_INVALID_TYPE + self::CLASS_CHECK_TYPE_NOT_IN_LIST
                )
                : new InvalidArgumentTypeException(
                    '$className',
                    $types,
                    $className,
                    self::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER + self::CLASS_CHECK_VALUE_IS_EMPTY + self::CLASS_CHECK_INVALID_TYPE
                );
        }

        // Validation for object instances
        if (is_object($className)) {
            foreach ($valid as $matchingClass => $isTrait) {
                if ($isTrait) {
                    if (in_array($matchingClass, self::classUsesTraits($className, false), true)) {
                        return get_class($className);
                    }
                } elseif ($className instanceof $matchingClass) {
                    return get_class($className);
                }
            }

            if (!$throwException) {
                return null;
            }

            throw new InvalidArgumentClassException(
                '$className',
                $types,
                $className,
                self::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER + self::CLASS_CHECK_TYPE_NOT_IN_LIST + self::CLASS_CHECK_VALUE_IS_INSTANCE
            );
        }

        if (!is_string($className)) {
            if (!$throwException) {
                return null;
            }

            throw new InvalidArgumentTypeException(
                '$className',
                $types,
                $className,
                self::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER + self::CLASS_CHECK_INVALID_TYPE
            );
        }

        $cleaned = preg_replace('/[^a-z0-9_\-\\\]/i', '', $className);

        if ($strict && $cleaned !== $className) {
            if (!$throwException) {
                return null;
            }

            throw new InvalidArgumentClassException(
                '$className',
                'a valid class name or an object instance',
                $className,
                self::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER
            );
        }

        $className = $cleaned;

        if (!class_exists($className)) {
            if (!$throwException) {
                return null;
            }

            throw new InvalidArgumentValueException(
                '$className',
                'a valid class name or an object instance',
                $className,
                self::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER + self::CLASS_CHECK_NON_EXISTING_CLASS
            );
        }

        foreach ($valid as $matchingClass => $isTrait) {
            if ($isTrait) {
                if (in_array($matchingClass, self::classUsesTraits($className, false), true)) {
                    return $className;
                }
            } elseif (is_a($className, $matchingClass, true)) {
                return $className;
            }
        }

        if (!$throwException) {
            return null;
        }

        throw new InvalidArgumentClassException(
            '$className',
            $types,
            $className,
            self::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER + self::CLASS_CHECK_TYPE_NOT_IN_LIST
        );
    }

    /**
     * @param mixed $value value to be tested or converted
     * @param bool $strict indicates if strict comparison should be performed:
     *  ``
     *  - if True, `$value` must already be of type `float`.
     *  - if False, a conversion to `float` is attempted.
     *  ``
     * @param bool $throwException throws an exception instead of returning `null`
     *
     * @since 1.16
     */
    public static function filterFloat($value, bool $strict = false, bool $throwException = false): ?float
    {
        $param = $throwException ? '$value' : false;
        if ($strict) {
            $types = ['float'];
            $input = $value;
        } else {
            $types = ['float', null];
            $input = filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
        }

        return self::checkType($input, $types, $param) === null ? null : $input;
    }

    /**
     * @param mixed $value value to be tested or converted
     * @param bool $strict indicates if strict comparison should be performed:
     * ``
     * - if True, `$value` must already be of type `int`.
     * - if False, a conversion to `int` is attempted.
     * ``
     * @param bool $throwException throws an exception instead of returning `null`
     *
     * @return int|null
     * @since 1.16
     */
    public static function filterInt($value, bool $strict = false, bool $throwException = false): ?int
    {
        $param = $throwException ? '$value' : false;

        if ($strict) {
            $types = ['integer'];
            $input = $value;
        } else {
            $types = ['integer', null];
            $input = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        }

        return self::checkType($input, $types, $param) === null ? null : $input;
    }

    /**
     * @param mixed $value value to be tested or converted
     * @param bool $strict indicates if strict comparison should be performed:
     * ``
     * - if True, `$value` must already be a scalar value.
     * - if False, `NULL` is also allowed (not throwing an exception, if `$throw` is True).
     * ``
     * @param bool $throwException throws an exception instead of returning `null`
     *
     * @since 1.16
     */
    public static function filterScalar($value, bool $strict = null, bool $throwException = false)
    {
        $param = $throwException ? '$value' : false;
        $types = $strict ? ['is_scalar'] : [null, 'is_scalar'];

        return self::checkType($value, $types, $param) === null ? null : $value;
    }

    /**
     * @param mixed $value value to be tested or converted
     * @param bool $strict indicates if strict comparison should be performed:
     * ``
     * - if True, `$value` must already be of type `string`.
     * - if False, a conversion to `string` is attempted.
     * ``
     * @param bool $throwException throws an exception instead of returning `null`
     *
     * @since 1.16
     */
    public static function filterString($value, bool $strict = false, bool $throwException = false): ?string
    {
        $param = $throwException ? '$value' : false;
        $types = $strict ? ['string'] : ['string', null, Stringable::class, 'is_scalar'];

        switch (self::checkType($value, $types, $param)) {
            case 'string':
                return $value;
            case 'NULL':
                return null;
            case Stringable::class:
                return $value->__toString();
            case 'is_scalar':
                return (string)$value;
        }

        return null;
    }

    /**
     * Method evaluates all the traits used in an object/class, including the ones inherited from parent classes
     *
     * @param string|object $class Class name or instance to be checked
     * @param bool $autoload Indicates whether autoload should be performed for classes that are not yet loaded.
     *
     * @return array an array of trait names used by the given class
     * @since 1.16
     * @see https://www.php.net/manual/en/function.class-uses.php#122427
     */
    public static function classUsesTraits($class, bool $autoload = true): array
    {
        $traits = [];

        // Get all the traits of $class and its parent classes
        do {
            $class_name = is_object($class) ? get_class($class) : $class;

            if (class_exists($class_name, $autoload)) {
                $traits = array_merge(class_uses($class, $autoload), $traits);
            }
        } while ($class = get_parent_class($class));

        // Get traits of all parent traits
        $traits_to_search = $traits;
        while (!empty($traits_to_search)) {
            $new_traits = class_uses(array_pop($traits_to_search), $autoload);
            $traits = array_merge($new_traits, $traits);
            $traits_to_search = array_merge($new_traits, $traits_to_search);
        };

        $traits = array_unique($traits);

        return $traits;
    }

    /**
     * @param $types
     *
     * @return array|string[]
     * @since 1.16
     */
    protected static function parseTypes($types): array
    {

        if ($types === null) {
            return ['NULL'];
        }

        if (is_array($types)) {
            if (count($types) === 0) {
                throw new InvalidArgumentValueException(
                    '$types cannot be empty',
                    ['string', 'string[]', null],
                    $types
                );
            }

            return $types;
        }

        if (is_string($types)) {
            if ($types === '') {
                throw new InvalidArgumentValueException(
                    '$types cannot be empty',
                    ['string', 'string[]', null],
                    $types
                );
            }

            return explode('|', $types);
        }

        throw new InvalidArgumentTypeException(
            '$types',
            ['string', 'string[]', null],
            $types
        );
    }
}
