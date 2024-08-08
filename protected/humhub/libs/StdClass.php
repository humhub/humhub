<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\libs;

use __PHP_Incomplete_Class;
use ArrayAccess;
use ArrayIterator;
use Countable;
use humhub\exceptions\InvalidArgumentTypeException;
use humhub\exceptions\InvalidArgumentValueException;
use humhub\exceptions\InvalidConfigTypeException;
use OutOfBoundsException;
use ReflectionClass;
use ReflectionException;
use ReflectionObject;
use ReflectionProperty;
use SeekableIterator;
use Serializable;
use Stringable;
use Traversable;
use Yii;
use yii\base\Arrayable;
use yii\base\ArrayableTrait;
use yii\helpers\ArrayHelper;

/**
 * WARNING: This class and its API is still in experimental state. Expect changes in 1.16 (ToDo)
 * ---
 *
 * This class provides an object that can have dynamic properties (like \stdClass) but with some additional features:
 * - the properties can also be access in the array-like manner, \
 *      i.e. `$object['property']` for `$object->property`
 * - `count()` can be used to get the number of properties
 * - the object can be cleared \
 *      with `static:clear()` or `static::addValue(null)`
 * - add multiple values in as an array \
 *      with `static::addValue($property1 => $value1, $property2 => $value2, ...)`
 * - add multiple array of value sets which will be merged first \
 *      with `static::addValue([$property1 => $value1, ...], [$property2 => $value2, ...], ...)`
 * - allows to verify if the entire object or a particular property has been modified \
 *      with `static::isModified()` \
 *      and `static::isFieldModified($field)`
 * - allows to retrieve the (un)modified fields \
 *      with `static::fieldsModified(true|false)` (`null` will return all fields)
 * - the internal array pointer can be moved \
 *      with `static::seek($pos)`
 * - the object is stringable (which by default uses serialize)
 * - provides a factory
 *      with `\humhub\libs\StdClass::create()`
 * - the serialization is optimized to
 *      - reduce data by only exporting modified versions of self, and using pre-defined short property keys
 *      - provide format versioning
 *      - only allow deserialization of self and subclasses of self
 *
 * @since 1.15 This class and its API is still in experimental state. Expect changes in 1.16 (ToDo)
 * @internal (ToDo)
 * @see static::addValues()
 * @see static::__serialize()
 * @see static::unserialize()
 */
class StdClass extends \stdClass implements ArrayAccess, Stringable, SeekableIterator, Countable, Serializable, Arrayable
{
    use ArrayableTrait;

    public const SERIALIZE_FORMAT = 1;

    protected const SERIALIZE_VALUE__VERSION = 'v';
    protected const SERIALIZE_VALUE__DATA = '_0';
    /**
     * List of extracted properties from the object to be unserialized.
     * - For optional properties, just add the property name to the list
     * - For *required* properties, use the following syntax: `[$propertyName => true]`
     */
    protected const UNSERIALIZE_REQUIRED_VALUES = [
        self::SERIALIZE_VALUE__VERSION => true,
        self::SERIALIZE_VALUE__DATA,
    ];

    /**
     * @var \stdClass|null
     */
    protected static ?\stdClass $validatedObject = null;

    /**
     * @param array|traversable|string|null ...$args see class description
     *
     * @return static
     * @throws InvalidConfigTypeException
     * @see          \humhub\libs\StdClass
     * @noinspection MagicMethodsValidityInspection
     * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
     */
    public function __construct(...$args)
    {
        return $this->addValues(...$args);
    }

    public function __get($name)
    {
        $this->validatePropertyName($name, __METHOD__);

        /** @noinspection PhpExpressionAlwaysNullInspection */
        return $this->$name ?? null;
    }

    public static function isSerializing(?self $object, bool $end = false): bool
    {
        static $current;

        if ($current === null) {
            if ($object) {
                $current = $object;
            }

            return false;
        }

        if ($end && $object && $object === $current) {
            $current = null;

            return false;
        }

        return true;
    }

    /**
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function __set($name, $value)
    {
        $this->validatePropertyName($name, __METHOD__);

        $this->$name = $value;

        return $this;
    }

    public function __isset($name)
    {
        return property_exists($this, $name);
    }

    public function __unset($name)
    {
        $name = $this->validatePropertyName($name, __METHOD__);

        unset($this->$name);

        return $this;
    }

    public function __toString()
    {
        return serialize($this);
    }

    public function __serialize(): array
    {
        $return = [
            self::SERIALIZE_VALUE__VERSION => self::SERIALIZE_FORMAT,
        ];

        $fields = $this->fieldsModified(true);

        if ($fields !== null) {
            $fields = array_fill_keys($fields, null);

            foreach ($fields as $field => &$value) {
                $value = $this->$field;
            }
            unset($value);

            $return[self::SERIALIZE_VALUE__DATA] = &$fields;
        }

        return $return;
    }

    /**
     * @param array|\stdClass $serialized
     *
     * @return self
     * @throws InvalidArgumentTypeException|InvalidConfigTypeException
     * @noinspection MagicMethodsValidityInspection
     */
    public function __unserialize($serialized)
    {
        $valid = $this->validateSerializedInput($serialized);

        // clear only after validation was successful
        $this->clear();

        if (!$valid || !property_exists($serialized, self::SERIALIZE_VALUE__DATA)) {
            return $this;
        }

        return $this->addValues($serialized->{self::SERIALIZE_VALUE__DATA});
    }

    /**
     * @see static::__serialize()
     */
    public function serialize(): string
    {
        return serialize($this);
    }

    /**
     * This function is automatically called when you run `unserialize($string)' (where `$string` includes this class)
     * or
     * - `new static($string)`
     * - `$object->unserialize($string)`
     * In any case, `$string` MUST start with the object definition of this class and MUST NOT contain any object other
     * than this class (self::class) or `\stdClass`
     *
     * @param string $serialized
     *
     * @return $this
     * @throws InvalidConfigTypeException
     */
    public function unserialize($serialized): self
    {
        if (!is_string($serialized)) {
            throw new InvalidArgumentTypeException(
                '$serialized',
                ["string"],
                $serialized,
            );
        }

        /**
         * If `self::class` and `static::class` are the same, they are merged into one key. That's intentional.
         *
         * @noinspection PhpDuplicateArrayKeysInspection
         */
        $allowedClasses = [
            self::class => self::class,
            static::class => static::class,
            \stdClass::class => null,
        ];

        /**
         * @var string|null $signature Will contain the signature found for the top-level object
         *
         * @noinspection AlterInForeachInspection
         */
        foreach ($allowedClasses as $class => &$signature) {
            if ($signature === null) {
                $allowedClasses = array_filter($allowedClasses);
                throw new InvalidArgumentValueException(
                    '$serialized',
                    sprintf("string starting with '%s'", implode(' or ', $allowedClasses)),
                    $serialized,
                );
            }

            $signature = sprintf('O:%s:"%s"', strlen($class), $class);

            if (str_starts_with($serialized, $signature)) {
                break;
            }
        }

        // replace the definition of the top-level object to be a \stdClass, so we can internally unserialize it and
        // retrieve the values needed to set up this instance
        $serialized = substr_replace($serialized, 'O:8:"stdClass"', 0, strlen($signature));

        // get the basic values of the serialized object
        $clone = unserialize($serialized, ['allowed_classes' => array_keys($allowedClasses)]);

        // now translate that into the real structure
        return $this->__unserialize($clone);
    }


    /**
     * @return static
     * @throws InvalidConfigTypeException
     * @see          static::__construct()
     * @noinspection PhpParamsInspection
     * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
     */
    public static function create(...$args): self
    {
        return new static(...$args);
    }

    public function clear(): self
    {
        foreach ((new ReflectionObject($this))->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            unset($this->{$property->getName()});
        }

        return $this;
    }

    /**
     * This method allows modifying the values of the current object:
     * - `static::addValue()`: nothing happens.
     *   This is useful for the case where you do `static::addValue(...$someArray)`, where `$someArray` is an empty
     * array.
     * - `static::addValue(null)`: This resets the entire set of stored values!
     *   This is equivalent to calling `static::clear()`
     * - `static::addValue($serializedString)`: Initializes the object with values from `$serializedString`.
     *    This is equivalent to calling `static::unserialize()`
     * - `static::addValue(array|Traversable $set_1, ...)`:
     *   Any number of arrays or objects implementing `\Traversable` - allowing `foreach($argument as $property =>
     * $value)`. If more than one set is provided, they are merged according to \`yii\helpers\BaseArrayHelper::merge()`
     *   (earlier values taking precedence), e.g.:
     *   - `static::addValue([$property => $value, ...])`
     *   - `static::addValue($traversableObject)`
     *
     * @param ...$args
     *
     * @return $this
     * @throws InvalidArgumentTypeException|InvalidConfigTypeException
     * @see static::unserialize();
     * @see \yii\helpers\BaseArrayHelper::merge();
     * @see static::clear()
     */
    public function addValues(...$args): self
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
                throw new InvalidArgumentTypeException('...$args', [
                    'array',
                    Traversable::class,
                ], $args);
            }
        }

        foreach ($args as $k => $v) {
            $this->$k = $v;
        }

        return $this;
    }

    public function count(): int
    {
        return count($this->fields());
    }

    public function isModified(): bool
    {
        return $this->fieldsModified(false, true) === null;
    }

    public function isFieldModified(string $field): ?bool
    {
        if (!$this->__isset($field)) {
            return null;
        }

        try {
            $property = new ReflectionProperty($this, $field);
        } catch (ReflectionException $e) {
            // Not an actual property. It may be the result of a getField() getter. So assume, it has been set.
            return true;
        }

        // check if the property has been defined in the class, or dynamically
        if (!$property->isDefault()) {
            // we always assume dynamically assigned properties as changed
            return true;
        }

        // static properties are ignored
        if ($property->isStatic()) {
            return null;
        }

        $data = $this->$field;

        /**
         * this only works as of PHP v8
         *
         * @ToDo remove version check and this comment when when min required version is 8.0
         * @noinspection PhpUndefinedMethodInspection
         */
        if (PHP_MAJOR_VERSION >= 8 && $property->hasDefaultValue() && $data === $property->getDefaultValue()) {
            return false;
        }

        if ($data instanceof self) {
            return $data->isModified();
        }

        // better be safe than sorry
        return true;
    }

    /**
     * @param bool|null $filter If null, both modified and unmodified fields are returned as `[$name => $modified]`
     *     pairs. If `true`, only modified fields will be returned. The field names are the array values! If `false`,
     *     only unmodified fields will be returned. The field names are the array values!
     *
     * @return array
     */
    public function &fieldsModified(?bool $filter = null, bool $failOnNoMatch = false): ?array
    {
        $fields = $this->fields();

        foreach ($fields as $field => &$status) {
            $status = $this->isFieldModified($field);

            if ($failOnNoMatch && $filter !== null && $status !== $filter) {
                $fields = null;

                return $fields;
            }
        }
        unset($status);

        $fields = array_filter($fields, static fn($value) => $filter === null ? $value !== null : $value === $filter);
        $fields = count($fields) ? $fields : null;

        if ($fields !== null && $filter !== null) {
            $fields = array_keys($fields);
        }

        return $fields;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator(get_object_vars($this));
    }

    /**
     * @inheritdoc
     * @noinspection PhpParamsInspection
     */
    public function current()
    {
        return current($this);
    }

    /**
     * @inheritdoc
     * @return string|int|null
     * @noinspection PhpParamsInspection
     */
    public function key()
    {
        return key($this);
    }

    /**
     * @inheritdoc
     * @noinspection PhpParamsInspection
     */
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
            $this->validatePropertyName($offset, __METHOD__, '$offset'),
        );
    }

    public function offsetSet($offset, $value)
    {
        return $this->__set(
            $this->validatePropertyName($offset, __METHOD__, '$offset'),
            $value,
        );
    }

    public function offsetUnset($offset)
    {
        return $this->__unset(
            $this->validatePropertyName($offset, __METHOD__, '$offset'),
        );
    }

    /**
     * @inheritdoc
     * @return static
     * @noinspection PhpParamsInspection
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function rewind()
    {
        reset($this);

        return $this;
    }

    /**
     * @inheritdoc
     * @return string|int the current key after seek
     * @noinspection PhpParamsInspection
     */
    public function seek($position)
    {
        if (
            !is_int($int = $position) && null === $int = filter_var(
                $position,
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE,
            )
        ) {
            throw new InvalidArgumentTypeException('$position', 'int', $position);
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

    /**
     * @inheritdoc
     * @noinspection PhpParamsInspection
     */
    public function valid(): bool
    {
        return key($this) !== null;
    }

    /**
     * This function is used internally to validate property names
     *
     * @param $name
     * @param string $method
     * @param string|array $parameter
     *
     * @return void
     */
    protected function validatePropertyName($name, string $method, string $parameter = '$name'): ?string
    {
        switch (true) {
            case is_string($name):
            case is_int($name):
            case $name instanceof Stringable:
                return $name;

            case is_bool($name):
                return (int)$name;
        }

        throw InvalidArgumentTypeException::newInstance(
            $parameter,
            ['string', 'int', 'bool', Stringable::class],
        )->setMethodName($method);
    }

    /**
     * @param array|\stdClass $serialized
     *
     * @return bool|null True if valid array, False if valid object, null on error
     * @throws InvalidConfigTypeException
     */
    protected function validateSerializedInput(&$serialized, ?array $requiredFields = self::UNSERIALIZE_REQUIRED_VALUES, bool $throw = true): ?bool
    {
        // this is used to identify already-validated data
        self::$validatedObject ??= self::validatedObject();

        if ($serialized instanceof self::$validatedObject) {
            return true;
        }

        if (is_array($serialized)) {
            $isObject = false;
        } elseif (!is_object($serialized) || get_class($serialized) !== \stdClass::class) {
            if (!$throw) {
                return null;
            }

            throw new InvalidArgumentTypeException(
                '$serialized',
                ['array', \stdClass::class],
                $serialized,
            );
        } else {
            $isObject = true;
        }

        $requiredFields ??= static::UNSERIALIZE_REQUIRED_VALUES;

        // check if the default version field (v) is explicitly not wanted
        if (($requiredFields[self::SERIALIZE_VALUE__VERSION] ?? true) === null) {
            unset($requiredFields[self::SERIALIZE_VALUE__VERSION]);
        } else {
            $requiredFields[self::SERIALIZE_VALUE__VERSION] = true;
        }

        // check if the default data field (_0) is explicitly not wanted
        if (($requiredFields[self::SERIALIZE_VALUE__DATA] ?? true) === null) {
            unset($requiredFields[self::SERIALIZE_VALUE__DATA]);
        } else {
            $requiredFields[self::SERIALIZE_VALUE__DATA] = false;
        }

        $result = [];

        foreach ($requiredFields as $field => $required) {
            if (is_int($field)) {
                $result[$required] = false;
            } else {
                $result[$field] = (bool)$required;
            }
        }

        $requiredFields = $result;

        $result = self::validatedObject();

        foreach ($requiredFields as $field => $required) {
            // allows us to check if the value was set
            $value = $this;

            /**
             * $serialized may be a \stdClass object created by `static::unserialize()`. So check for the required property
             * based on the given data type
             *
             * @see static::unserialize()
             */
            if ($isObject) {
                if (method_exists($serialized, '__isset') ? $serialized->__isset($field) : property_exists($serialized, $field)) {
                    $value = &$serialized->{$field};
                }
            } elseif (is_array($serialized)) {
                if (array_key_exists($field, $serialized)) {
                    $value = &$serialized[$field];
                }
            }

            // check if a value has been retrieved
            if ($value === $this) {
                if (!$required) {
                    continue;
                }

                if (!$throw) {
                    return false;
                }

                throw new InvalidArgumentValueException(
                    sprintf('Required field %s not found in serialized data for %s', $field, static::class),
                );
            }

            if (!$this->validateClassIncomplete($value, $found)) {
                if (!$throw) {
                    return false;
                }

                throw new InvalidArgumentTypeException(
                    sprintf('Invalid classes found in serialized data: %s', implode(', ', array_filter($found))),
                );
            }

            $result->$field = &$value;

            // destroy reference
            unset($value);
        }

        $serialized = $result;

        return true;
    }

    /**
     * @throws InvalidConfigTypeException
     */
    protected function validateClassInheritance($class, bool $throw, bool $strict = true): bool
    {
        if (!is_string($class)) {
            if (!$throw) {
                return false;
            }

            throw new InvalidConfigTypeException(sprintf(
                "Invalid class property type for %s: %s",
                static::class,
                get_debug_type($class),
            ), 1);
        }

        if (!class_exists($class)) {
            if (!$throw) {
                return false;
            }

            throw new InvalidConfigTypeException(sprintf(
                "Invalid class name or non-existing class for %s: %s",
                static::class,
                get_debug_type($class),
            ), 2);
        }

        $parentClass = $strict ? static::class : self::class;

        if ($class !== $parentClass && !is_subclass_of($class, $parentClass)) {
            if (!$throw) {
                return false;
            }

            throw new InvalidConfigTypeException(sprintf(
                "Class %s is not a subclass of %s",
                get_debug_type($class),
                $parentClass,
            ), 3);
        }

        return true;
    }

    /**
     * @param iterable|\stdClass|null $serialized
     * @param array|null $found Output parameter returning
     *        - `null` if no incomplete class wos found, or
     *        - a list of class names as key and boolean value indicating that the class has been
     *          - invalid (true) or
     *          - valid (false).
     *        To get a list of all invalid classes, use `array_flip(array_filter((array)$found))`
     * @param bool $throw
     * @param int|null $recursion recursion will be performed for the level as indicated by this parameter.
     *        If `null` the default value of 1 will be applied
     *
     * @return bool
     * @throws InvalidConfigTypeException
     */
    protected function validateClassIncomplete(&$serialized, ?array &$found = null, bool $throw = true, ?int $recursion = 1): bool
    {
        $found = [];
        $recursion ??= 1;

        if ($serialized === null || is_scalar($serialized)) {
            return true;
        }

        foreach ($serialized as $key => $item) {
            if (is_scalar($item)) {
                continue;
            }

            $changed = false;

            if ($item instanceof __PHP_Incomplete_Class) {
                // data of the incomplete class can only be accessed through iteration
                $data = [];
                $class = null;

                foreach ($item as $property => $value) {
                    if ($property === '__PHP_Incomplete_Class_Name') {
                        // check if the class name inherits from StdClass
                        $validateClassInheritance = $this->validateClassInheritance($value, $throw, false);

                        // Save the result for return value.
                        // We store `true` for invalid, `false` for valid classes.
                        // This allows easy filtering invalid classes by using `array_filter($found)`
                        $found[$value] = !$validateClassInheritance;

                        if ($validateClassInheritance) {
                            // would be an alternative, but possibly requires more computational resources:
                            // $subitem = unserialize(serialize($subitem), ['allowed_classes' => [self::class, static::class, StdClass::class, $value]]);

                            // create a reflection class to later instantiate the new object
                            try {
                                $class = new ReflectionClass($value);
                            } catch (ReflectionException $e) {
                                $found[$value] = true;

                                Yii::warning(
                                    sprintf(
                                        "Reflection Exception occurred while validating metadata! %s",
                                        serialize($item),
                                    ),
                                    'File',
                                );

                                continue 2;
                            }

                            // loop through the other properties
                            continue;
                        }

                        Yii::warning(
                            sprintf("Invalid metadata found and removed! %s", serialize($item)),
                            'File',
                        );

                        $item = null;

                        // skip the other properties and go to the next `$subitem`
                        continue 2;
                    }

                    // store the data in our array
                    $data[$property] = $value;
                }

                // now create an instance without calling the constructor ...
                $item = $class->newInstanceWithoutConstructor();

                // ... and initialize the object with the obtained data (as `unserialize` would do)
                $item->__unserialize($data);

                $changed = true;
                $data = null;
                $class = null;
            } elseif ((is_iterable($item) || $item instanceof \stdClass) && $recursion) {
                $incomplete = $this->validateClassIncomplete($item, $newFound, $throw, $recursion - 1);

                $found = ArrayHelper::merge($found, (array)$newFound);

                if (!$incomplete) {
                    return false;
                }

                $changed = true;
            }

            if ($changed) {
                if (is_array($serialized) || $serialized instanceof ArrayAccess) {
                    $serialized[$key] = $item;
                } else {
                    $serialized->$key = $item;
                }
            }
        }

        if ($found === null) {
            return true;
        }

        // since valid classes carry a `false` value, while invalid classes carry a `true` value,
        // we can simply filter the array and only invalid classes will remain
        return empty(array_filter($found));
    }

    protected static function validatedObject(): \stdClass
    {
        if (self::$validatedObject === null) {
            self::$validatedObject = new class extends \stdClass {
            };
        }

        $class = get_class(self::$validatedObject);

        return new $class();
    }
}
