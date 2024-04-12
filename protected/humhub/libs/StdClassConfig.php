<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\libs;

use Error;
use humhub\exceptions\InvalidConfigTypeException;
use ReflectionClass;
use ReflectionException;
use ReflectionObject;
use RuntimeException;
use SplObjectStorage;
use Throwable;
use UnexpectedValueException;
use WeakReference;
use yii\base\UnknownPropertyException;

/**
 * WARNING: This class and its API is still in experimental state. Expect changes in 1.16 (ToDo)
 * ---
 *
 * @codingStandardsIgnoreFile PSR2.Classes.PropertyDeclaration.Underscore
 * @since 1.15 This class and its API is still in experimental state. Expect changes in 1.16 (ToDo)
 * @internal (ToDo)
 */
class StdClassConfig extends StdClass
{
    public const SERIALIZE_FORMAT = 1;
    protected const SERIALIZE_VALUE__PARENT_FIXED = '_1';
    protected const SERIALIZE_VALUE__CONFIG_FIXED = '_2';
    protected const SERIALIZE_VALUE__CLASS = 'class';

    protected const UNSERIALIZE_REQUIRED_VALUES = [
        self::SERIALIZE_VALUE__CLASS,
        self::SERIALIZE_VALUE__PARENT_FIXED,
        self::SERIALIZE_VALUE__CONFIG_FIXED,
    ];

    /**
     * @var mixed|null
     */
    public $default;

    // StdClassConfigurable meta-properties

    protected bool $__StdClassConfigurable_isFixed = false;

    /**
     * @var bool Denotes if the parent StdClass is loading, i.e., running the __construct() method
     */
    protected bool $__StdClassConfigurable_loading = true;

    // StdClassConfig meta-properties

    /**
     * @var bool Denotes if dynamic properties can be added (false) or the set of properties is fix.
     */
    protected bool $__StdClassConfig_isFixed = false;

    private static SplObjectStorage $__StdClassConfig_config;

    /**
     * @var WeakReference Holding a reference to teh parent objet without increasing the reference count
     * @see static::getParent()
     * @see static::getReflection()
     */
    private WeakReference $__StdClassConfig_parent;

    /**
     * @param StdClassConfigurable $parent
     * @param mixed ...$initialValues
     *
     * @noinspection MagicMethodsValidityInspection
     * @throws InvalidConfigTypeException
     */
    public function __construct(StdClassConfigurable $parent, ...$initialValues)
    {
        $this->__StdClassConfig_parent = WeakReference::create($parent);

        return parent::__construct(...$initialValues);
    }

    /**
     * @throws UnknownPropertyException
     */
    public function __get($name)
    {
        if ($this->__StdClassConfig_isFixed && !$this->__StdClassConfigurable_loading) {
            throw new UnknownPropertyException('Getting unknown property: ' . static::class . '::$' . $name);
        }

        return parent::__get($name);
    }

    /**
     * @throws UnknownPropertyException
     */
    public function __set($name, $value)
    {
        if ($this->__StdClassConfig_isFixed && !$this->__StdClassConfigurable_loading) {
            throw new UnknownPropertyException('Setting unknown property: ' . static::class . '::$' . $name);
        }

        return parent::__set($name, $value);
    }

    public function __serialize(): array
    {
        $data = parent::__serialize();

        /**
         * ToDo: this can be removed in PHP 8
         *
         * @see StdClass::isFieldModified()
         */
        if (PHP_MAJOR_VERSION < 8) {
            if (($data[self::SERIALIZE_VALUE__DATA]['default'] ?? null) === null) {
                unset($data[self::SERIALIZE_VALUE__DATA]['default']);
            }

            if (count($data[self::SERIALIZE_VALUE__DATA]) === 0) {
                unset($data[self::SERIALIZE_VALUE__DATA]);
            }
        }

        if ($this->__StdClassConfigurable_isFixed) {
            $data[self::SERIALIZE_VALUE__PARENT_FIXED] = $this->__StdClassConfigurable_isFixed;
        }

        if ($this->__StdClassConfig_isFixed) {
            $data[self::SERIALIZE_VALUE__CONFIG_FIXED] = $this->__StdClassConfig_isFixed;
        }

        if (static::class !== self::class) {
            $data['class'] = static::class;
        }

        return $data;
    }

    /**
     * @param array|\stdClass $serialized
     *
     * @return StdClassConfig
     * @noinspection MagicMethodsValidityInspection
     * @throws InvalidConfigTypeException
     */
    public function __unserialize($serialized)
    {
        $this->validateSerializedInput($serialized);

        $class = $serialized->class ?? static::class;
        unset($serialized->class);

        // check if different class is required!
        if ($class !== static::class) {

            try {
                $class = new ReflectionClass($class);
                $config = $class->newInstanceWithoutConstructor();

                return $config->__unserialize($serialized);
            } catch (ReflectionException $e) {
            }
        }

        $parentFixed = $serialized->{self::SERIALIZE_VALUE__PARENT_FIXED} ?? false;
        unset($serialized->{self::SERIALIZE_VALUE__PARENT_FIXED});

        $configFixed = $serialized->{self::SERIALIZE_VALUE__CONFIG_FIXED} ?? false;
        unset($serialized->{self::SERIALIZE_VALUE__CONFIG_FIXED});

        $config = $this;

        parent::__unserialize($serialized);

        $this->__StdClassConfigurable_isFixed = $parentFixed;
        $this->__StdClassConfig_isFixed = $configFixed;

        return $config;
    }

    public function clear(): self
    {
        if ($this->isLoading()) {
            return $this;
        }

        parent::clear();

        $this->default = null;

        return $this;
    }

    public function isLoading(): bool
    {
        return $this->__StdClassConfigurable_loading;
    }

    public function setLoading(bool $loading): self
    {
        $this->__StdClassConfigurable_loading = $loading;

        return $this;
    }

    public function isFixed(): bool
    {
        return $this->__StdClassConfigurable_isFixed && !$this->__StdClassConfigurable_loading;
    }

    /**
     * Once called, no (further) dynamic properties can be added
     *
     * @see          static::$__StdClassConfig_isFixed
     * @noinspection PhpUnused
     */
    public function fixate(bool $fixed): self
    {
        $this->__StdClassConfigurable_isFixed = $this->__StdClassConfig_isFixed || $fixed;

        return $this;
    }

    /**
     * @return bool
     * @noinspection PhpUnused
     */
    public function isConfigFixed(): bool
    {
        return $this->__StdClassConfig_isFixed;
    }

    /**
     * Once called, no (further) dynamic properties can be added
     *
     * @see          static::$__StdClassConfig_isFixed
     * @noinspection PhpUnused
     */
    public function fixateConfig(bool $fixed): self
    {
        $this->__StdClassConfig_isFixed = $this->__StdClassConfig_isFixed || $fixed;

        return $this;
    }

    public function isModified(): bool
    {
        return $this->__StdClassConfigurable_isFixed
            || $this->__StdClassConfig_isFixed
            || parent::isModified();
    }


    public function getReflection(): ReflectionObject
    {
        return new ReflectionObject($this->__StdClassConfig_parent->get() ?? new \stdClass());
    }

    /**
     * @throws InvalidConfigTypeException
     */
    public static function &getConfig(): self
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3);

        $i = 1;
        $destroy = $trace[$i]['class'] === static::class && $trace[$i]['function'] === 'destroyConfig';

        if ($destroy) {
            ++$i;
        }

        $parent = $trace[$i]['object'] ?? null;

        if (!$parent instanceof StdClassConfigurable) {
            throw new RuntimeException(sprintf(
                'Method %s can only be called from a %s instance itself',
                __METHOD__,
                StdClassConfigurable::class,
            ));
        }

        try {
            $config = self::$__StdClassConfig_config[$parent];
        } catch (UnexpectedValueException $e) {
            $config = null;
        } catch (Error $e) {
            if ($e->getMessage() === 'Typed static property ' . self::class . '::$__StdClassConfig_config must not be accessed before initialization') {
                self::$__StdClassConfig_config = new SplObjectStorage();
                $config = null;
            }
        }

        if ($destroy) {
            if ($config) {
                unset(self::$__StdClassConfig_config[$parent]);
            }
        } elseif ($config === null) {
            self::$__StdClassConfig_config[$parent] = $config = new static($parent);
        }

        return $config;
    }

    /**
     * @throws Throwable
     * @internal
     */
    public static function destroyConfig(): void
    {
        static::getConfig();
    }

    /**
     * @return mixed
     */
    public function getParent(): ?StdClassConfigurable
    {
        return $this->__StdClassConfig_parent->get();
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

        if ($class === null || $this->validateClassInheritance($class, $throw)) {
            return $valid;
        }

        return null;
    }
}
