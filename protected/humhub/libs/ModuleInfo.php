<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use Error;
use ReflectionClass;
use ReflectionException;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\base\UnknownPropertyException;

/**
 * @author Martin Rüegg
 *
 * @property string $id
 * @property-read null|string $classFilePath
 * @property-read null|string $classFileDir
 * @property-read bool $classExists
 * @property-read bool $isActive
 */
class ModuleInfo extends BaseObject
{
    // constants
    public const MISSING_MODULE = '\humhub\missing_module';

    //  public properties
    public string $moduleId = '';
    public string $class = '';
    public ?string $namespace = null;
    public ?string $configBasePath = null;
    public bool $isMissing = false;
    public bool $isRegistered = false;
    public bool $isSuspended = false;
    public bool $isCoreModule = false;
    public bool $isInstallerModule = false;
    public array $aliases = [];
    public array $events = [];
    public array $modules = [];

    // protected properties
    protected string $classFilePath;
    protected ?bool $classExists = null;


    final private function __construct($config = [])
    {
        parent::__construct($config);
    }


    public function __set(
        $name,
        $value
    ) {
        try {
            parent::__set($name, $value);
        } catch (UnknownPropertyException | InvalidCallException $t) {
            return;
        }
    }


    /**
     * @param bool $autoload
     *
     * @return bool
     */
    public function getClassExists(bool $autoload = true): bool
    {
        if ($this->classExists !== null) {
            return $this->classExists;
        }

        if ($this->class === '') {
            return $this->classExists = false;
        }

        if (class_exists($this->class, $autoload)) {
            return $this->classExists = true;
        }

        if ($autoload === false) {
            return false;
        }

        /**
         * check if module alias has already been set
         *
         * @see \humhub\components\ModuleManager::register
         */
        if (Yii::getAlias('@' . $this->moduleId, false) === false) {
            throw new InvalidCallException(
                sprintf(
                    '%s may only be called, once the alias for the module (@%s) is set',
                    __METHOD__,
                    $this->moduleId
                )
            );
        }

        $this->classExists = false;

        return false;
    }


    /**
     * @return string|null The path found by the class name or an empty string for PHP core or extension classes, or
     *                     null if not found. Please not, that unless $isMissing is set, the path may be found once the
     *                     alias is set
     */
    public function getClassFileDir(): ?string
    {
        $file = $this->getClassFilePath();

        return ($file !== null)
            ? dirname($file)
            : null;
    }


    /**
     * @return string|null The path found by the class name or an empty string for PHP core or extension classes, or
     *                     null if not found. Please not, that unless $isMissing is set, the path may be found once the
     *                     alias is set
     */
    public function getClassFilePath(): ?string
    {
        try {
            // this will fail, as long as the path has not been set
            return $this->classFilePath;
        } catch (Error $t) {
            if (! $this->getClassExists()) {
                return null;
            }

            try {
                $reflection = new ReflectionClass($this->class);

                return $this->classFilePath = $reflection->getFileName()
                    ?: '';
            } catch (ReflectionException $e) {
                return null;
            }
        }
    }


    public function getId(): string
    {
        return $this->moduleId;
    }


    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->isRegistered && ! $this->isSuspended && ! $this->isMissing && $this->getClassExists();
    }


    /**
     * @param string $moduleId
     */
    public function setId(string $moduleId): void
    {
        $this->moduleId = $moduleId;
    }


    /**
     * @param array|static $config
     * @param string|null $configBasePath
     *
     * @return static
     * @throws \yii\base\InvalidConfigException
     */
    public static function instantiate(
        $config = [],
        ?string $configBasePath = null
    ): self {
        if (! $config instanceof self) {
            $config = new static($config);
        }

        if ($configBasePath) {
            $config->configBasePath = $configBasePath;
        }

        return self::validate($config);
    }


    /**
     * @throws \yii\base\InvalidConfigException
     */
    private static function validate(self $moduleInfo): self
    {
        if ($moduleInfo->moduleId === '' || (! $moduleInfo->isMissing && $moduleInfo->class === '')) {
            throw new InvalidConfigException(
                'Module configuration requires an id and class attribute: ' . $moduleInfo->configBasePath
            );
        }

        return $moduleInfo;
    }
}
