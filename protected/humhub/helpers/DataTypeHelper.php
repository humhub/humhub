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
    public const TYPE_CHECK_VALUE_IS_NULL = 128;
    public const TYPE_CHECK_TYPE_NOT_IN_LIST = 32;
    public const TYPE_CHECK_INVALID_TYPE_PARAMETER = 2;
    public const TYPE_CHECK_VALUE_IS_EMPTY = 4;
    public const TYPE_CHECK_INVALID_VALUE_PARAMETER = 1;
    public const TYPE_CHECK_INVALID_TYPE = 8;
    public const TYPE_CHECK_NON_EXISTING_CLASS = 16;
    public const TYPE_CHECK_VALUE_IS_INSTANCE = 64;

    public const BOOLEAN = 'boolean';
    public const INTEGER = 'integer';
    public const STRING = 'string';
    public const ARRAY = 'array';
    public const OBJECT = 'object';
    public const RESOURCE = 'resource';
    public const RESOURCE_CLOSED = 'resource (closed)';
    public const UNKNOWN_TYPE = 'unknown type';
    public const DOUBLE = 'double';
    public const FLOAT = 'float';
    public const NULL = 'NULL';


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
     *      rather that it's name.
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
    public static function matchType($value, $allowedTypes, bool $throwException = false): ?string
    {
        $validTypes = self::parseTypes($allowedTypes, $allowNull, $checkTraits);

        if ($value === null) {
            if ($allowNull) {
                return self::NULL;
            }

            if (!$throwException) {
                return null;
            }

            throw new InvalidArgumentTypeException(
                '$value',
                $validTypes,
                $value,
                self::TYPE_CHECK_INVALID_VALUE_PARAMETER + self::TYPE_CHECK_VALUE_IS_EMPTY + self::TYPE_CHECK_INVALID_TYPE + self::TYPE_CHECK_VALUE_IS_NULL,
            );
        }

        $inputType = gettype($value);
        $inputTraits = $checkTraits ? self::classUsesTraits($value, false) : null;

        foreach ($allowedTypes as $i => $typeToCheck) {
            if (static::matchTypeHelper($typeToCheck, $value, $inputType, $inputTraits) !== null) {
                return $validTypes[$i];
            }
        }

        if (!$throwException) {
            return null;
        }

        $code = self::TYPE_CHECK_INVALID_VALUE_PARAMETER + self::TYPE_CHECK_TYPE_NOT_IN_LIST;

        if (is_object($value)) {
            $code |= self::TYPE_CHECK_VALUE_IS_INSTANCE;
        }

        throw new InvalidArgumentClassException(
            '$value',
            $allowedTypes,
            $value,
            $code,
        );
    }

    protected static function matchTypeHelper($typeToCheck, &$input, string $inputType, ?array &$inputTraits = null): ?string
    {

        if (is_string($typeToCheck) || (is_object($typeToCheck) && $typeToCheck = get_class($typeToCheck))) {
            switch ($typeToCheck) {
                case self::STRING:
                case self::ARRAY:
                case self::OBJECT:
                case self::RESOURCE:
                case self::RESOURCE_CLOSED: // as of PHP 7.2.0
                case self::NULL:
                case self::UNKNOWN_TYPE:
                    return $inputType === $typeToCheck
                        ? $typeToCheck
                        : null;

                case self::BOOLEAN:     // the result of gettype()
                case 'bool':            // the name as it is defined in code
                    return $inputType === self::BOOLEAN
                        // return it the way it was tested
                        ? $typeToCheck
                        : null;

                case self::INTEGER:     // the result of gettype()
                case 'int':             // the name as it is defined in code
                    return $inputType === self::INTEGER
                        // return it the way it was tested
                        ? $typeToCheck
                        : null;

                case self::DOUBLE:
                case self::FLOAT:
                    return $inputType === self::DOUBLE
                        // return it the way it was tested
                        ? $typeToCheck
                        : null;

                case Stringable::class:
                    return $inputType === self::OBJECT
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
     * Ensures that the provided `$value` is of or implements any class, interface or trait as provided by the
     * `$allowedTypes` parameter. The function throws an Exception if none of the provided types is matched. Please see
     * self::matchClassType() for more information on the parameters.
     *
     * @see self::matchClassType()
     * @throws InvalidArgumentTypeException|InvalidArgumentClassException|InvalidArgumentValueException
     * @since 1.16
     */
    public static function ensureClassType($value, $allowedTypes): void
    {
        self::matchClassType($value, $allowedTypes, true);
    }

    /**
     * Ensures that the provided `$value` is of or implements any type as provided by the `$allowedTypes` parameter.
     * The function throws an Exception if none of the provided types is matched.
     * Please see self::matchType() for more information on the parameters.
     *
     * @since 1.16
     * @see self::matchType()
     * @see InvalidArgumentTypeException
     * @throws InvalidArgumentTypeException|InvalidArgumentValueException
     */
    public static function ensureType($value, $allowedTypes): void
    {
        self::matchType($value, $allowedTypes, true);
    }

    /**
     * Helper variable that returns the input `$value` if it is matched against the `$allowedTypes`
     *
     * @return mixed|null The `$value` if it matches any type given in $allowedTypes, or NULL otherwise
     *
     * @since 1.16
     * @see self::matchType()
     * @see InvalidArgumentTypeException
     */
    private static function filterType($value, $allowedTypes, bool $throwException = false)
    {
        return self::matchType($value, $allowedTypes, $throwException) === null
            ? null
            : $value;
    }

    /**
     * Returns the boolean value of `$value`, or NULL if it's not a boolean and cannot be converted.
     * See the parameter description for more information.
     *
     * @param mixed $value value to be tested or converted
     * @param bool $strict indicates if strict comparison should be performed:
     * ``
     * - if TRUE, `$value` must already be of type `boolean`. In that case, its value is returned, NULL otherwise.
     * - if FALSE, a conversion to `boolean` is attempted using `(bool)`. No exception is thrown, ever.
     *       Note: "false", "off", "no" yield TRUE in this case.
     * - if NULL, a conversion to `boolean` is attempted, where
     *      "1", "true", "on", and "yes" yield TRUE,
     *      "0", "false", "off", "no", and "" yield FALSE.
     * ``
     * @param bool $throwException throws an exception instead of returning `null`
     *
     * @return bool|null see `$strict` parameter for details
     *
     * @see filter_var()
     * @since 1.16
     */
    public static function filterBool($value, ?bool $strict = false, bool $throwException = false): ?bool
    {
        if ($strict) {
            return self::filterType($value, [self::BOOLEAN], $throwException);
        }

        if ($strict === false) {
            try {
                return (bool)$value;
            } catch (\Throwable $e) {
            }
            return false;
        }

        $input = is_array($value) ? !empty($value) : filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE,
        );

        return self::filterType($input, [self::BOOLEAN, null], $throwException);
    }

    /**
     * Checks if the class or object has one of the given classes, interfaces or traits as one of its parents or
     * implements it.
     *
     * @param string|object|null|mixed $value Object or classname to be checked. Null may be valid if included in
     *      $type. Everything else is invalid and either throws an error (default) or returns NULL, if $throw is false.
     * @param string|string[]|object[] $allowedTypes (List of) allowed class, interface or trait names, or object
     *      instances. Object instances may only be passed as part of an array. In such a case, the object's type/class
     *      is used for comparison. If a string is provided, it will be split by `|`. If NULL value or the "NULL"
     *     string
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
                self::TYPE_CHECK_INVALID_VALUE_PARAMETER + self::TYPE_CHECK_VALUE_IS_EMPTY + self::TYPE_CHECK_INVALID_TYPE + self::TYPE_CHECK_VALUE_IS_NULL,
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
                    self::TYPE_CHECK_INVALID_VALUE_PARAMETER + self::TYPE_CHECK_VALUE_IS_EMPTY + self::TYPE_CHECK_INVALID_TYPE + self::TYPE_CHECK_TYPE_NOT_IN_LIST,
                )
                : new InvalidArgumentTypeException(
                    '$value',
                    $allowedTypes,
                    $value,
                    self::TYPE_CHECK_INVALID_VALUE_PARAMETER + self::TYPE_CHECK_VALUE_IS_EMPTY + self::TYPE_CHECK_INVALID_TYPE,
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
                    self::TYPE_CHECK_INVALID_VALUE_PARAMETER + self::TYPE_CHECK_NON_EXISTING_CLASS,
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
                self::TYPE_CHECK_INVALID_VALUE_PARAMETER + self::TYPE_CHECK_INVALID_TYPE,
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

        $code = self::TYPE_CHECK_INVALID_VALUE_PARAMETER + self::TYPE_CHECK_TYPE_NOT_IN_LIST;

        if ($isObject) {
            $code |= self::TYPE_CHECK_VALUE_IS_INSTANCE;
        }

        throw new InvalidArgumentClassException(
            '$value',
            $allowedTypes,
            $value,
            $code,
        );
    }

    /**
     * Returns the boolean value of `$value`, or NULL if it's not a float and cannot be converted.
     * See the parameter description for more information.
     *
     * @param mixed $value value to be tested or converted
     * @param bool $strict indicates if strict comparison should be performed:
     *  ``
     *  - if TRUE, `$value` must already be of type `float`.
     *  - if FALSE, a conversion to `float` is attempted.
     *  ``
     * @param bool $throwException throws an exception instead of returning `null`
     *
     * @since 1.16
     */
    public static function filterFloat($value, bool $strict = false, bool $throwException = false): ?float
    {
        if ($strict) {
            return self::filterType($value, [self::FLOAT], $throwException);
        }

        $input = filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);

        return self::filterType($input, [self::FLOAT, null], $throwException);
    }

    /**
     * Returns the boolean value of `$value`, or NULL if it's not an integer and cannot be converted.
     * See the parameter description for more information.
     *
     * @param mixed $value value to be tested or converted
     * @param bool $strict indicates if strict comparison should be performed:
     * ``
     * - if TRUE, `$value` must already be of type `int`.
     * - if FALSE, a conversion to `int` is attempted.
     * ``
     * @param bool $throwException throws an exception instead of returning `null`
     *
     * @return int|null
     * @since 1.16
     */
    public static function filterInt($value, bool $strict = false, bool $throwException = false): ?int
    {
        if ($strict) {
            return self::filterType($value, [self::INTEGER], $throwException);
        }

        $input = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

        return self::filterType($input, [self::INTEGER, null], $throwException);
    }

    /**
     * Returns the boolean value of `$value`, or NULL if it's not a scalar.
     * See the parameter description for more information.
     *
     * @param mixed $value value to be tested or converted
     * @param bool $strict indicates if strict comparison should be performed:
     * ``
     * - if TRUE, `$value` must already be a scalar value.
     * - if FALSE, `NULL` is also allowed (not throwing an exception, if `$throw` is TRUE).
     * ``
     * @param bool $throwException throws an exception instead of returning `null`
     *
     * @return bool|int|float|string|null
     * @since 1.16
     */
    public static function filterScalar($value, bool $strict = false, bool $throwException = false)
    {
        if ($strict) {
            return self::filterType($value, ['is_scalar'], $throwException);
        }

        return self::filterType($value, [null, 'is_scalar'], $throwException);
    }

    /**
     * Returns the boolean value of `$value`, or NULL if it's not a string and cannot be converted.
     * See the parameter description for more information.
     *
     * @param mixed $value value to be tested or converted
     * @param bool $strict indicates if strict comparison should be performed:
     * ``
     * - if TRUE, `$value` must already be of type `string`.
     * - if FALSE, a conversion to `string` is attempted.
     * ``
     * @param bool $throwException throws an exception instead of returning `null`
     *
     * @since 1.16
     */
    public static function filterString($value, bool $strict = false, bool $throwException = false): ?string
    {
        $allowedTypes = $strict ? [self::STRING] : [self::STRING, null, Stringable::class, 'is_scalar'];

        switch (self::matchType($value, $allowedTypes, $throwException)) {
            case self::STRING:
                return $value;

            case self::NULL:
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
                    [self::STRING, 'string[]', 'object[]'],
                    $allowedTypes,
                    self::TYPE_CHECK_INVALID_TYPE_PARAMETER + self::TYPE_CHECK_VALUE_IS_EMPTY,
                );
            }

            $allowedTypes = explode('|', $allowedTypes);
        }

        if (!is_array($allowedTypes)) {
            throw new InvalidArgumentTypeException(
                '$allowedTypes',
                [self::STRING, 'string[]', 'object[]', null],
                $allowedTypes,
                self::TYPE_CHECK_INVALID_TYPE_PARAMETER + self::TYPE_CHECK_INVALID_TYPE,
            );
        }

        if (count($allowedTypes) === 0) {
            throw new InvalidArgumentValueException(
                '$allowedTypes',
                [self::STRING, 'string[]', 'object[]'],
                $allowedTypes,
                self::TYPE_CHECK_INVALID_TYPE_PARAMETER + self::TYPE_CHECK_VALUE_IS_EMPTY,
            );
        }

        $valid = [];

        // validate the type array
        foreach ($allowedTypes as $index => &$item) {
            if ($item === null || $item === self::NULL) {
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
                    ['class', self::OBJECT],
                    $item,
                    self::TYPE_CHECK_INVALID_TYPE_PARAMETER + self::TYPE_CHECK_INVALID_TYPE,
                );
            }

            if ($allowGetTypes) {
                switch ($item) {
                    case self::BOOLEAN:     // the result of gettype()
                    case 'bool':            // the name as it is defined in code
                    case self::INTEGER:     // the result of gettype()
                    case 'int':             // the name as it is defined in code
                    case self::STRING:
                    case self::ARRAY:
                    case self::OBJECT:
                    case self::RESOURCE:
                    case self::RESOURCE_CLOSED: // as of PHP 7.2.0
                    case self::UNKNOWN_TYPE:
                    case self::DOUBLE:
                    case self::FLOAT:
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
                self::TYPE_CHECK_INVALID_TYPE_PARAMETER + self::TYPE_CHECK_NON_EXISTING_CLASS,
            );
        }

        return $valid;
    }
}
