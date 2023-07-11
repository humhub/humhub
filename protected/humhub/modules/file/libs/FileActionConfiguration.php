<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\file\libs;

use ReflectionException;
use ReflectionMethod;
use Yii;
use yii\base\BaseObject;
use yii\base\InlineAction;
use yii\base\InvalidConfigException;

/**
 *
 * @property-read string $fileListParameterBase
 */
class FileActionConfiguration extends BaseObject
{
    public const EVENT_INIT = 'init';

    /**
     * Specify the name for the parameter used to store the files
     *
     * @see \yii\web\UploadedFile::getInstancesByName()
     * @var string
     */
    protected string $fileListParameterName = 'files[]';
    protected ?array $propertyInformation   = null;
    protected FileControllerInterface $controller;

    protected string $action;
    protected ReflectionMethod $method;
    protected ?bool $allowMultiple = null;

    /**
     * @param FileControllerInterface $controller
     * @param string                  $action
     * @param                         $config
     *
     * @throws InvalidConfigException
     * @noinspection PhpDocRedundantThrowsInspection
     */
    public function __construct(FileControllerInterface $controller, string $action, $config = [])
    {
        $this->controller = $controller;
        $this->action     = $action;

        parent::__construct($config);
    }

    /**
     * @throws InvalidConfigException
     * @noinspection RedundantSuppression
     */
    public function init()
    {
        if ($this->action === '') {
            throw new InvalidConfigException(sprintf('%s::$action cannot be empty', __CLASS__), 1);
        }

        $responseFormat = Yii::$app->response->format;

        if (null === $action = $this->controller->createAction($this->action)) {
            Yii::$app->response->format = $responseFormat;

            throw new InvalidConfigException(
                sprintf("'Controller %s doesn't support the action %s'", get_class($this->controller), $this->action),
                2
            );
        }

        /** @noinspection SuspiciousAssignmentsInspection */
        Yii::$app->response->format = $responseFormat;

        try {
            if ($action instanceof InlineAction) {
                $this->method = new ReflectionMethod($this->controller, $action->actionMethod);
            } else {
                $this->method = new ReflectionMethod($action, 'run');
            }
        } catch (ReflectionException $e) {
            throw new InvalidConfigException(
                sprintf(
                    "Controller %s  has an invalid configuration for action %s",
                    get_class($this->controller),
                    $this->action
                ),
                3,
                $e
            );
        }
    }

    public function getAllowMultiple(): bool
    {
        return $this->allowMultiple ??= str_ends_with($this->fileListParameterName, '[]');
    }

    /**
     * @return string
     */
    public function getFileListParameterBase(): string
    {
        return rtrim($this->fileListParameterName, '[]');
    }

    /**
     * @return string
     */
    public function getFileListParameterName(): string
    {
        return $this->fileListParameterName;
    }

    /**
     * @param string $fileListParameterName
     *
     * @return FileActionConfiguration
     */
    public function setFileListParameterName(string $fileListParameterName): FileActionConfiguration
    {
        $this->fileListParameterName = $fileListParameterName;
        $this->allowMultiple         = null;

        return $this;
    }

    /**
     * @return array
     */
    public function getPropertyInformation(): array
    {
        return $this->propertyInformation ??= array_map(static fn(
            \ReflectionParameter $p
        ) => new ActionParameterConfiguration($p), $this->method->getParameters());
    }

    /**
     * @param array|null $propertyInformation
     *
     * @return FileActionConfiguration
     */
    public function setPropertyInformation(?array $propertyInformation): FileActionConfiguration
    {
        $this->propertyInformation = $propertyInformation;

        return $this;
    }
}
