<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\file\libs;

use humhub\components\Event;
use humhub\exceptions\InvalidArgumentTypeException;
use humhub\libs\StdClass;
use humhub\libs\StdClassConfigurable;
use humhub\modules\file\models\File;
use Yii;
use yii\base\InvalidConfigException;

/**
 * WARNING: This class and its API is still in experimental state. Expect changes in 1.16 (ToDo)
 * ---
 *
 * This class is used to access the data in the `File::$metadata` field.
 *
 * Data MUST be stored under the top-level key with the name of the module the metadata belongs to and MUST be of type
 * StdClass, or a subclass of it.
 *
 * @since 1.15; this class and its API is still in experimental state. Expect changes in 1.16 (ToDo)
 * @internal (ToDo)
 *
 * @see File::$metadata
 * @see StdClass
 */
final class Metadata extends StdClassConfigurable
{
    public const EVENT_INIT = self::class . '::INIT';
    public const EVENT_REGISTER =  self::class . '::REGISTER';

    public FileModuleMetadata $file;

    // @codingStandardsIgnoreStart PSR2.Methods.MethodDeclaration.Underscore
    private array $__Metadata_registered = [];
    private array $__Metadata_enabled;

    // @codingStandardsIgnoreEnd PSR2.Methods.MethodDeclaration.Underscore

    public function __construct(...$args)
    {
        // unset properties so that the first access calls the magic functions
        unset($this->file);

        // get enabled modules
        $this->__Metadata_enabled = Yii::$app->moduleManager->getModules([
            'includeCoreModules' => true,
            'returnClass' => true
        ]);

        Event::trigger($this, self::EVENT_INIT);

        parent::__construct(...$args);
    }

    public function __get($name)
    {
        // this is a trick to invoke __set() to validate the name, create a new instance to the property,
        //but without having to invoke `__set()` again from within `__set()`
        $this->$name = $this;

        // now return the value
        return $this->$name;
    }

    /**
     * @throws InvalidConfigException|InvalidArgumentTypeException
     */
    public function __set($name, $value)
    {
        try {
            $name = $this->validatePropertyName($name, __METHOD__);
        } catch (InvalidArgumentTypeException $e) {
            if ($e->getCode() === 1) {
                // the module $name has not registered this property.

                // Let's try to assign it a null value. This will fail for the typed properties defined in this class.
                // Hence, check for type error
                try {
                    $this->$name = null;
                } catch (\TypeError $r) {
                    // in this case, it seems to be a configuration error
                    throw new InvalidConfigException(sprintf(
                        "Module '%s' has not registered it's metadata namespace '%s'.",
                        $this->__Metadata_enabled[$name],
                        $name
                    ));
                }

                return $this;
            }

            throw $e;
        }

        if ($value === $this) {
            $class = $this->__Metadata_registered[$name];
            $value = new $class();
        }

        if ($value !== null && !$value instanceof StdClass) {
            throw new InvalidArgumentTypeException('$value', [StdClass::class, null], $value);
        }

        $this->$name = $value;

        return $this;
    }

    public function __isset($name)
    {
        return $this->validatePropertyName($name, __METHOD__) !== null;
    }

    /**
     * @inheritdoc
     */
    public function fields(): array
    {
        $fields = array_keys($this->__Metadata_registered);
        return array_combine($fields, $fields);
    }

    /**
     * @param string $name
     * @param string|StdClass|true $metadataClass
     *
     * @return true
     * @throws InvalidConfigException|InvalidArgumentTypeException
     */
    public function register(string $name, $metadataClass): bool
    {
        $event = new Event();
        $event->result = [
            'name' => $name,
            'metadataClass' => $metadataClass,
        ];

        Event::trigger($this, self::EVENT_REGISTER, $event);

        $name = $event->result['name'] ?? null;
        $metadataClass = $event->result['metadataClass'] ?? null;

        if ($name === null || $metadataClass === null) {
            return false;
        }

        $class = $this->__Metadata_enabled[$name] ?? null;

        if ($class === null) {
            throw new InvalidConfigException(sprintf("%s is not the ID of an enabled module!", $name));
        }

        $instance = null;

        if (is_object($metadataClass)) {
            $instance = $metadataClass;
            $metadataClass = get_class($instance);
        } elseif ($metadataClass === true) {
            $metadataClass = StdClass::class;
        } elseif (!is_string($metadataClass)) {
            throw new InvalidArgumentTypeException(
                '$metadataClass',
                ['className or instance of ' . StdClass::class],
                $metadataClass
            );
        }

        // now get the registered class, if available
        $class = $this->__Metadata_registered[$name] ?? null;

        if ($class === $metadataClass) {
            return true;
        }

        if ($class !== null) {
            throw new InvalidConfigException(sprintf(
                "An error occurred while registering the metadata class %s for Module %s: it was previously registered as %s",
                $metadataClass,
                $name,
                $class
            ));
        }

        if (
            !$instance instanceof StdClass
            && $instance !== null
            && !($metadataClass === StdClass::class || is_subclass_of($metadataClass, StdClass::class))
        ) {
            throw new InvalidArgumentTypeException(
                '$metadataClass',
                [StdClass::class],
                $instance
            );
        }

        $this->__Metadata_registered[$name] = $metadataClass;

        if ($instance) {
            $this->$name = $instance;
        } else {
            unset($this->$name);
        }

        return true;
    }

    /**
     * @param $name
     * @param string $method
     * @param string $parameter
     *
     * @return string|null
     * @throws InvalidArgumentTypeException
     */
    protected function validatePropertyName($name, string $method, string $parameter = '$name'): ?string
    {
        $property = StdClass::validatePropertyName($name, $method, $parameter);

        if (!$property || ($this->__Metadata_registered[$property] ?? false) === false) {
            throw new InvalidArgumentTypeException(
                $parameter,
                $this->__Metadata_enabled,
                $name,
                1
            );
        }

        return $property;
    }
}
