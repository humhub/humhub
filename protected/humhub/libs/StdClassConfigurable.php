<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\exceptions\InvalidArgumentTypeException;
use humhub\exceptions\InvalidConfigTypeException;
use Throwable;
use yii\base\UnknownPropertyException;

/**
 * WARNING: This class and its API is still in experimental state. Expect changes in 1.16 (ToDo)
 * ---
 *
 * Extending from \humhub\libs\StdClass, this class some additional features:
 * - a defaultValue can be set which will be used in case a non-existent property is read: \
 *      `static::setDefaultValue($value)`
 *      `static::getDefaultValue()`
 * - the object provides a static::config() accessor to a separate property namespace
 * - allows the object to be fixated, so that now additional property can be added \
 *      with `static::fixate()`
 *      (this is not write-protection, just disallowing new properties!)
 *
 * @since 1.15 This class and its API is still in experimental state. Expect changes in 1.16 (ToDo)
 * @internal (ToDo)
 * @see StdClassConfig
 */
class StdClassConfigurable extends StdClass
{
    public const SERIALIZE_FORMAT = 1;
    protected const SERIALIZE_VALUE__CLASS = 'class';
    protected const SERIALIZE_VALUE__CONFIG = 'config';

    protected const UNSERIALIZE_REQUIRED_VALUES = [
        self::SERIALIZE_VALUE__DATA,
        self::SERIALIZE_VALUE__CLASS,
        self::SERIALIZE_VALUE__CONFIG,
    ];

    /**
     * @inerhitdoc
     * @noinspection MagicMethodsValidityInspection
     */
    public function __construct(...$args)
    {
        $config = $this->config();

        parent::__construct(...$args);

        $config->setLoading(false);

        return $this;
    }

    public function __destruct()
    {
        try {
            StdClassConfig::destroyConfig();
        } catch (Throwable $e) {
        }
    }

    /**
     * @throws UnknownPropertyException
     */
    public function __get($name)
    {
        if ($name === null) {
            return $this->getDefaultValue();
        }

        if ($this->config()->isFixed()) {
            throw new UnknownPropertyException('Getting unknown property: ' . static::class . '::$' . $name);
        }

        return parent::__get($name);
    }

    /**
     * @throws UnknownPropertyException
     */
    public function __set($name, $value)
    {
        if ($name === null) {
            return $this->setDefaultValue($value);
        }

        if ($this->config()->isFixed()) {
            throw new UnknownPropertyException('Setting unknown property: ' . static::class . '::$' . $name);
        }

        return parent::__set($name, $value);
    }

    public function __isset($name)
    {
        if ($name === null) {
            return $this->hasDefaultValue();
        }

        return parent::__isset($name);
    }

    public function __unset($name)
    {
        if ($name === null) {
            $this->setDefaultValue(null);

            return $this;
        }

        return parent::__unset($name);
    }

    public function __serialize(): array
    {
        $isSerializing = self::isSerializing($this);

        $data = parent::__serialize();

        /**
         * Loop through the data and remove any unmodified instance of `StdClass`.
         *
         * (Use $item as a reference so that arrays don't get copied.)
         *
         * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection
         */
        foreach ($data[self::SERIALIZE_VALUE__DATA] ?? [] as $key => &$item) {
            if ($item instanceof StdClass && !$item->isModified()) {
                unset($data[self::SERIALIZE_VALUE__DATA][$key]);
            }
        }
        unset($item);

        if ($isSerializing && static::class !== self::class) {
            $data[self::SERIALIZE_VALUE__CLASS] = static::class;
        }

        $data[self::SERIALIZE_VALUE__CONFIG] = (object)$this->config()->__serialize();

        self::isSerializing($this, !$isSerializing);

        return $data;
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
        /**
         * $serialized may be a \stdClass object created by `static::unserialize()`
         *
         * @see static::unserialize()
         */
        $this->validateSerializedInput($serialized);

        // clear only after validation was successful
        $this->clear();

        $config = &$serialized->{self::SERIALIZE_VALUE__CONFIG};
        unset($serialized->{self::SERIALIZE_VALUE__CONFIG});

        $return = parent::__unserialize($serialized);

        $return->config()
            ->__unserialize($config)
            ->setLoading(false);

        return $return;
    }

    protected function &config(): StdClassConfig
    {
        return StdClassConfig::getConfig();
    }

    public function clear(): self
    {
        $config = $this->config();

        if ($config->isLoading()) {
            return $this;
        }

        $config->clear();

        return parent::clear();
    }

    public function isModified(): bool
    {
        return parent::isModified() || $this->config()->isModified();
    }

    public function isFixed(): bool
    {
        return $this->config()->isFixed();
    }

    /**
     * Once called, no (further) dynamic properties can be added
     *
     * @see          StdClassConfig::fixate()
     * @noinspection PhpUnused
     */
    public function fixate(): self
    {
        $this->config()->fixate(true);

        return $this;
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
        if ($name === null) {
            return null;
        }

        return parent::validatePropertyName($name, $method, $parameter);
    }

    /**
     * @throws InvalidConfigTypeException
     */
    protected function validateSerializedInput(&$serialized, ?array $requiredFields = self::UNSERIALIZE_REQUIRED_VALUES, bool $throw = true): ?bool
    {
        // this is used to identify already-validated data
        self::$validatedObject ??= self::validatedObject();

        if ($serialized instanceof self::$validatedObject) {
            return true;
        }

        $valid = parent::validateSerializedInput($serialized, $requiredFields, $throw);

        if ($valid === null) {
            return null;
        }

        $class = $serialized->{self::SERIALIZE_VALUE__CLASS} ?? null;

        if ($class !== null && !$this->validateClassInheritance($class, $throw)) {
            return null;
        }

        $config = &$serialized->{self::SERIALIZE_VALUE__CONFIG};

        $this->validateClassIncomplete($config);

        return $valid;
    }
}
