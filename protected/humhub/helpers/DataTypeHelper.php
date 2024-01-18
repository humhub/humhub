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

use function gettype;

/**
 * @since 1.16
 */
class DataTypeHelper
{
    public const CLASS_CHECK_VALUE_IS_NULL = 128;
    public const CLASS_CHECK_TYPE_NOT_IN_LIST = 32;
    public const CLASS_CHECK_INVALID_TYPE_PARAMETER = 2;
    public const CLASS_CHECK_VALUE_IS_EMPTY = 4;
    public const CLASS_CHECK_INVALID_VALUE_PARAMETER = 1;
    public const CLASS_CHECK_INVALID_TYPE = 8;
    public const CLASS_CHECK_NON_EXISTING_CLASS = 16;
    public const CLASS_CHECK_VALUE_IS_INSTANCE = 64;


    /**
     * @param mixed $value Variable to be checked.
     * @param string|string[]|object[] $allowedTypes Allowed types.
     *
     * @since 1.16
     * @see self::matchClassType()
     */
    public static function isClassType($value, $allowedTypes): bool
    {
        return self::matchClassType($value, $allowedTypes) !== null;
    }

    /**
     * @param mixed $value Variable to be checked.
     * @param string|string[]|object[] $allowedTypes Allowed types.
     *
     * @since 1.16
     * @see self::matchType()
     */
    public static function isType($value, $allowedTypes): bool
    {
        return self::matchType($value, $allowedTypes) !== null;
    }

    /**
     * @param mixed $value Variable to be checked.
     * @param string|string[]|object[] $allowedTypes Allowed types. Valid input are
     * ``
     * - simple type names as returned by gettype()
     * - `null` value or `'NULL'` string
     * - class, interface, or trait names
     * - class instances whose class type will be checked
     * - `callable`, e.g. `is_scalar`
     * ``
     * @param bool $returnIndex if set to `true`, the method will return the types' index, rather that it's name.
     *
     * @return string|null Returns the first match of `$value` against the `$allowedTypes`.
     * ``
     * - If the matched type is a `NULL` value, the string "NULL" is returned.
     * - If the matched type is an object instance, its class name is returned.
     * - If the matched type is a `callable`, the callable´s string representation (name) is returned.
     * - If no match is found, a `NULL` value is returned.
     *``
     * @throws InvalidArgumentTypeException|InvalidArgumentValueException
     * @since 1.16
     * @see gettype()
     */
    public static function matchType($value, $allowedTypes, bool $throwException = false, bool $returnIndex = false): ?string
    {
        $validTypes = self::parseTypes($allowedTypes, $allowNull, $checkTraits);

        if ($value === null) {
            if ($allowNull) {
                return $returnIndex ? array_search(null, $allowedTypes, true) : 'NULL';
            }

            if (!$throwException) {
                return null;
            }

            throw new InvalidArgumentTypeException(
                '$value',
                $validTypes,
                $value,
                self::CLASS_CHECK_INVALID_VALUE_PARAMETER + self::CLASS_CHECK_VALUE_IS_EMPTY + self::CLASS_CHECK_INVALID_TYPE + self::CLASS_CHECK_VALUE_IS_NULL
            );
        }

        $inputType = gettype($value);
        $inputTraits = $checkTraits ? self::classUsesTraits($value, false) : null;

        foreach ($allowedTypes as $i => $typeToCheck) {
            if (static::matchTypeHelper($typeToCheck, $value, $inputType, $inputTraits) !== null) {
                return $returnIndex
                    ? $i
                    : $validTypes[$i];
            }
        }

        if (!$throwException) {
            return null;
        }

        $code = self::CLASS_CHECK_INVALID_VALUE_PARAMETER + self::CLASS_CHECK_TYPE_NOT_IN_LIST;

        if (is_object($value)) {
            $code |= self::CLASS_CHECK_VALUE_IS_INSTANCE;
        }

        throw new InvalidArgumentClassException(
            '$value',
            $allowedTypes,
            $value,
            $code
        );
    }

    protected static function matchTypeHelper($typeToCheck, &$input, string $inputType, ?array &$inputTraits = null): ?string
    {

        if (is_string($typeToCheck) || (is_object($typeToCheck) && $typeToCheck = get_class($typeToCheck))) {
            switch ($typeToCheck) {
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

                case 'double':
                case 'float':
                    return $inputType === 'double'
                        // return it the way it was tested
                        ? $typeToCheck
                        : null;

                case Stringable::class:
                    return $inputType === 'object'
                    && ($input instanceof Stringable || is_callable([$input, '__toString']))
                        ? $typeToCheck
                        : null;

                default:
                    /**
                     * Autoload is not used in any of the following functions, since the $typeToCheck has already been loaded in `self::parseTypes()`
                     *
                     * @see          self::parseTypes()
                     * @noinspection NotOptimalIfConditionsInspection
                     */
                    if (
                        (class_exists($typeToCheck, false) || interface_exists($typeToCheck, false))
                        && $input instanceof $typeToCheck
                    ) {
                        return $typeToCheck;
                    }

                    if (
                        $inputTraits !== null
                        && trait_exists($typeToCheck, false)
                        && in_array($typeToCheck, $inputTraits, true)
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
     * @see self::matchClassType()
     * @throws InvalidArgumentTypeException|InvalidArgumentClassException|InvalidArgumentValueException
     * @since 1.16
     */
    public static function ensureClassType($value, $allowedTypes): string
    {
        return self::matchClassType($value, $allowedTypes, true);
    }

    /**
     * @since 1.16
     * @see self::matchType()
     * @see InvalidArgumentTypeException
     * @throws InvalidArgumentTypeException|InvalidArgumentValueException
     */
    public static function ensureType($value, $allowedTypes): string
    {
        return self::matchType($value, $allowedTypes, true);
    }

    /**
     * @param mixed $value Variable to be checked.
     * @param string|string[]|object[] $allowedTypes Allowed types. Valid input are
     * ``
     * - simple type names as returned by gettype()
     * - `null` value or `'NULL'` string
     * - class, interface, or trait names
     * - class instances whose class type will be checked
     * - `callable`, e.g. `is_scalar`
     * ``
     * @param bool $throwException
     *
     * @return string The name of the first matched type given in $allowedTypes
     *
     * @since 1.16
     * @see self::matchType()
     * @see InvalidArgumentTypeException
     */
    public static function filterType($value, $allowedTypes, bool $throwException = false)
    {
        return self::matchType($value, $allowedTypes, $throwException) === null
            ? null
            : $value;
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
        if ($strict) {
            return self::filterType($value, ['bool'], $throwException);
        }

        if ($strict === null) {
            try {
                return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool)$value;
            } catch (\Throwable $e) {
            }
            return false;
        }

        $input = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return self::matchType($input, ['bool', null], $throwException) === null ? null : $input;
    }

    /**
     * Checks if the class has this class as one of its parents
     *
     * @param string|object|null|mixed $value Object or classname to be checked. Null may be valid if included in
     *     $type. Everything else is invalid and either throws an error (default) or returns NULL, if $throw is false.
     * @param string|string[]|object[] $allowedTypes (List of) allowed class, interface or trait names, or object
     *     instances. Object instances may only be passed as part of an array. In such a case, the object's type/class
     *     is used for comparison. If a string is provided, it will be split by `|`. If NULL value or the "NULL" string
     *     is included, NULL values are also allowed.
     * @param bool $throwException throws an exception instead of returning `null`
     *
     * @return string|object|null
     * @throws InvalidArgumentTypeException|InvalidArgumentClassException|InvalidArgumentValueException
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpUnhandledExceptionInspection
     * @since 1.16
     */
    public static function filterClassType($value, $allowedTypes, bool $throwException = false)
    {
        return self::matchClassType($value, $allowedTypes, $throwException) === null
            ? null
            : $value;
    }

    /**
     * Checks if the class has this class as one of its parents
     *
     * @param string|object|null|mixed $value Object or classname to be checked. Null may be valid if included in
     *      $type. Everything else is invalid and either throws an error (default) or returns NULL, if $throw is false.
     * @param string|string[]|object[] $allowedTypes (List of) allowed class, interface or trait names, or object
     *      instances. Object instances may only be passed as part of an array. In such a case, the object's type/class
     *      is used for comparison. If a string is provided, it will be split by `|`. If NULL value or the "NULL" string
     *      is included, NULL values are also allowed.   *
     * @param bool $throwException throws an exception instead of returning `null`.
     * Code of the thrown Exception is a bit-mask consisting of the following bits:
     * ``
     *   - self::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER: Invalid $className parameter
     *   - self::CLASS_CHECK_INVALID_TYPE_PARAMETER: Invalid $type parameter
     *   - self::CLASS_CHECK_VALUE_IS_EMPTY: Empty parameter
     *   - self::CLASS_CHECK_INVALID_TYPE: Invalid type
     *   - self::CLASS_CHECK_NON_EXISTING_CLASS: Non-existing class
     *   - self::CLASS_CHECK_TYPE_NOT_IN_LIST: Class that is not in $type parameter
     *   - self::CLASS_CHECK_VALUE_IS_INSTANCE: $className is an object instance
     *   - self::CLASS_CHECK_VALUE_IS_NULL: NULL value
     * ``
     *
     * @return string|null
     * @throws InvalidArgumentTypeException|InvalidArgumentClassException|InvalidArgumentValueException
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpUnhandledExceptionInspection
     * @since 1.16
     */
    public static function matchClassType($value, $allowedTypes, bool $throwException = false): ?string
    {
        $allowedTypes = static::parseTypes($allowedTypes, $allowNull, $checkTraits, false, false);

        // check for null input
        if ($value === null) {
            // check if null is allowed
            if ($allowNull) {
                return null;
            }

            if (!$throwException) {
                return null;
            }

            throw new InvalidArgumentTypeException(
                '$value',
                $allowedTypes,
                $value,
                self::CLASS_CHECK_INVALID_VALUE_PARAMETER + self::CLASS_CHECK_VALUE_IS_EMPTY + self::CLASS_CHECK_INVALID_TYPE + self::CLASS_CHECK_VALUE_IS_NULL
            );
        }

        // check for other empty input values
        if (empty($value)) {
            if (!$throwException) {
                return null;
            }

            /** @noinspection PhpUnhandledExceptionInspection */
            throw is_string($value)
                ? new InvalidArgumentClassException(
                    '$value',
                    $allowedTypes,
                    $value,
                    self::CLASS_CHECK_INVALID_VALUE_PARAMETER + self::CLASS_CHECK_VALUE_IS_EMPTY + self::CLASS_CHECK_INVALID_TYPE + self::CLASS_CHECK_TYPE_NOT_IN_LIST
                )
                : new InvalidArgumentTypeException(
                    '$value',
                    $allowedTypes,
                    $value,
                    self::CLASS_CHECK_INVALID_VALUE_PARAMETER + self::CLASS_CHECK_VALUE_IS_EMPTY + self::CLASS_CHECK_INVALID_TYPE
                );
        }

        if ($checkTraits) {
            $checkTraits = self::classUsesTraits($value, false);
        }

        if ($isObject = is_object($value)) {
            $type = get_class($value);
        } elseif (is_string($value)) {
            if (!class_exists($value)) {
                if (!$throwException) {
                    return null;
                }

                throw new InvalidArgumentValueException(
                    '$value',
                    'a valid class name or an object instance',
                    $value,
                    self::CLASS_CHECK_INVALID_VALUE_PARAMETER + self::CLASS_CHECK_NON_EXISTING_CLASS
                );
            }

            $type = $value;
        } else {
            if (!$throwException) {
                return null;
            }

            throw new InvalidArgumentTypeException(
                '$value',
                $allowedTypes,
                $value,
                self::CLASS_CHECK_INVALID_VALUE_PARAMETER + self::CLASS_CHECK_INVALID_TYPE
            );
        }

        foreach ($allowedTypes as $matchingClass) {
            if ($checkTraits !== null && in_array($matchingClass, $checkTraits, true)) {
                return $type;
            }

            if ($isObject ? $value instanceof $matchingClass : is_a($value, $matchingClass, true)) {
                return $type;
            }
        }

        if (!$throwException) {
            return null;
        }

        $code = self::CLASS_CHECK_INVALID_VALUE_PARAMETER + self::CLASS_CHECK_TYPE_NOT_IN_LIST;

        if ($isObject) {
            $code |= self::CLASS_CHECK_VALUE_IS_INSTANCE;
        }

        throw new InvalidArgumentClassException(
            '$value',
            $allowedTypes,
            $value,
            $code
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
        if ($strict) {
            return self::filterType($value, ['float'], $throwException);
        }

        $input = filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);

        return self::filterType($input, ['float', null], $throwException);
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
        if ($strict) {
            return self::filterType($value, ['integer'], $throwException);
        }

        $input = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

        return self::filterType($input, ['integer', null], $throwException);
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
    public static function filterScalar($value, bool $strict = false, bool $throwException = false)
    {
        $allowedTypes = $strict ? ['is_scalar'] : [null, 'is_scalar'];

        return self::filterType($value, $allowedTypes, $throwException);
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
        $allowedTypes = $strict ? ['string'] : ['string', null, Stringable::class, 'is_scalar'];

        switch (self::matchType($value, $allowedTypes, $throwException)) {
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

        return array_unique($traits);
    }

    /**
     * @return string[]
     * @throws InvalidArgumentTypeException|InvalidArgumentValueException
     * @since 1.16
     */
    protected static function parseTypes(&$allowedTypes, ?bool &$allowNull = false, ?array &$checkTraits = null, bool $allowCallables = true, bool $allowGetTypes = true): array
    {
        $allowNull = false;
        $checkTraits = null;

        if ($allowedTypes === null) {
            $allowNull = true;
            $allowedTypes = [null];
            return [];
        }

        if (is_string($allowedTypes)) {
            if ($allowedTypes === '') {
                throw new InvalidArgumentValueException(
                    '$allowedTypes',
                    ['string', 'string[]', 'object[]'],
                    $allowedTypes,
                    self::CLASS_CHECK_INVALID_TYPE_PARAMETER + self::CLASS_CHECK_VALUE_IS_EMPTY
                );
            }

            $allowedTypes = explode('|', $allowedTypes);
        }

        if (!is_array($allowedTypes)) {
            throw new InvalidArgumentTypeException(
                '$allowedTypes',
                ['string', 'string[]', 'object[]', null],
                $allowedTypes,
                self::CLASS_CHECK_INVALID_TYPE_PARAMETER + self::CLASS_CHECK_INVALID_TYPE
            );
        }

        if (count($allowedTypes) === 0) {
            throw new InvalidArgumentValueException(
                '$allowedTypes',
                ['string', 'string[]', 'object[]'],
                $allowedTypes,
                self::CLASS_CHECK_INVALID_TYPE_PARAMETER + self::CLASS_CHECK_VALUE_IS_EMPTY
            );
        }

        $valid = [];

        // validate the type array
        foreach ($allowedTypes as $index => &$item) {
            if ($item === null || $item === 'NULL') {
                $allowNull = true;
                $item = null;
                continue;
            }

            if (is_object($item)) {
                $item = $valid[$index] = get_class($item);
                continue;
            }

            if ($allowCallables && is_callable($item, false, $name)) {
                $valid[$index] = $name;
                continue;
            }

            if (!is_string($item)) {
                throw new InvalidArgumentValueException(
                    sprintf('$allowedTypes[%s]', $index),
                    ['class', 'object'],
                    $item,
                    self::CLASS_CHECK_INVALID_TYPE_PARAMETER + self::CLASS_CHECK_INVALID_TYPE
                );
            }

            if ($allowGetTypes) {
                switch ($item) {
                    case 'boolean':     // the result of gettype()
                    case 'bool':        // the name as it is defined in code
                    case 'integer':     // the result of gettype()
                    case 'int':         // the name as it is defined in code
                    case 'string':
                    case 'array':
                    case 'object':
                    case 'resource':
                    case 'resource (closed)': // as of PHP 7.2.0
                    case 'unknown type':
                    case 'double':
                    case 'float':
                        $valid[$index] = $item;
                        continue 2;
                }
            }

            // Here autoload is active, so the class is loaded even if it is an interface or a trait
            if (class_exists($item)) {
                $valid[$index] = $item;
                continue;
            }

            // Here autoload is no longer required. See above.
            if (interface_exists($item, false)) {
                $valid[$index] = $item;
                continue;
            }

            // Here autoload is no longer required. See above.
            if (trait_exists($item, false)) {
                $valid[$index] = $item;
                $checkTraits[] = $item;
                continue;
            }

            throw new InvalidArgumentValueException(
                sprintf('$allowedTypes[%s]', $index),
                'a valid class/interface/trait name or an object instance',
                $item,
                self::CLASS_CHECK_INVALID_TYPE_PARAMETER + self::CLASS_CHECK_NON_EXISTING_CLASS
            );
        }

        return $valid;
    }
}
