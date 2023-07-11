<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2017-2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\file\libs;

use humhub\components\FileAction;
use humhub\modules\file\models\File;
use humhub\modules\file\models\FileInterface;
use ReflectionMethod;
use Throwable;
use Yii;
use yii\base\InlineAction;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;

/**
 * BaseFileController provides handles file up- and downloads and deletions
 *
 * @since 1.15
 *
 * @property-read array[][] $accessRules
 */
trait FileControllerTrait
{
    public bool $actionConfigDetection = false;

    // protected properties
    protected array $actionConfiguration = [];


    public function getFile(FileAction $action): ?FileInterface
    {
        return new $action->fileClass();
    }


    public function getActionConfiguration(
        string $actionName,
        bool $throwException = true
    ): ?FileActionConfiguration {

        try {
            return $this->actionConfiguration[ $actionName ] ??= new FileActionConfiguration($this, $actionName);
        } catch (InvalidConfigException $e) {
            if ($throwException) {
                /** @noinspection PhpUnhandledExceptionInspection */
                throw  $e;
            }
        }

        return null;
    }


    public function getFileListParameterBase(
        ?string $actionName = 'upload',
        bool $throwException = true
    ): ?string {

        return $this->getActionConfiguration(
            $actionName ?? FileControllerInterface::ACTION_UPLOAD,
            $throwException
        )->fileListParameterBase;
    }


    public function getFileListParameterName(
        ?string $actionName = 'upload',
        bool $throwException = true
    ): ?string {

        return $this->getActionConfiguration(
            $actionName ?? FileControllerInterface::ACTION_UPLOAD,
            $throwException
        )->fileListParameterName ?? null;
    }


    /**
     * @return true[]|Response
     * @throws HttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete()
    {

        $this->forcePostRequest();

        $guid = Yii::$app->request->post('guid');
        $file = File::findOne([ 'guid' => $guid ])
            ?: null;

        if ($file === null) {
            throw new HttpException(404, Yii::t('FileModule.base', 'Could not find requested file!'));
        }

        if (!$file->canDelete()) {
            throw new HttpException(401, Yii::t('FileModule.base', 'Insufficient permissions!'));
        }

        $file->delete();

        Yii::$app->response->format = 'json';

        return [ 'success' => true ];
    }


    /**
     * @inheritdoc
     */
    public function actions(): array
    {

        $actions = [];

        if (property_exists(static::class, 'downloadActionClass') && static::$downloadActionClass !== null) {
            $actions[ FileControllerInterface::ACTION_DOWNLOAD ] = [
                'class' => static::$downloadActionClass,
            ];
        }

        if (property_exists(static::class, 'uploadActionClass') && static::$uploadActionClass !== null) {
            $actions[ FileControllerInterface::ACTION_UPLOAD ] = [
                'class' => static::$uploadActionClass,
            ];
        }

        if (property_exists(static::class, 'deleteActionClass') && static::$deleteActionClass !== null) {
            $actions[ FileControllerInterface::ACTION_DELETE ] = [
                'class' => static::$deleteActionClass,
            ];
        }

        return $actions;
    }


    /**
     * @throws BadRequestHttpException
     */
    public function bindActionParams(
        $action,
        $params
    ): array {

        try {
            if ($action instanceof InlineAction) {
                $method = new ReflectionMethod($this, $action->actionMethod);
            } else {
                $method = new ReflectionMethod($action, 'run');
            }
        } catch (\ReflectionException $e) {
            throw new BadRequestHttpException(
                $e->getMessage(),
                1588,
                $e
            );
        }

        $requestedParams = $method->getParameters();
        $name            = null;

        if (count($requestedParams) && ( $last = end($requestedParams) ) && $last->isVariadic()) {
            $name = $last->getName();

            if (!array_key_exists($name, $params)) {
                // copy by reference so that the other defined parameters get removed
                $params[ $name ] = true;
            }
        }

        $newParams = parent::bindActionParams($action, $params);

        if ($name !== null) {
            $params                      = array_diff_key($params, $this->actionParams);
            $this->actionParams[ $name ] = $params;
            array_splice($newParams, count($newParams) - 1, 1, [ $params ]);
        }

        return $newParams;
    }


    /**
     * @param string|null $id the ID of this controller.
     * @param Module|null $module the module that this controller belongs to.
     * @param array|null $config name-value pairs that will be used to initialize the object properties.
     * @param bool $throwException
     *
     * @return static|FileControllerTrait|Controller|null
     * @throws Throwable
     */
    public static function create(
        ?string $id = null,
        ?Module $module = null,
        ?array $config = [],
        ?bool $throwException = true
    ): ?Controller {

        $throwException ??= true;
        $config         ??= [];
        $id             ??= $config['id'] ?? null;
        $module         ??= $config['module'] ?? null;

        unset($config['id'], $config['module']);

        try {
            return new static($id, $module, $config ?? []);
        } catch (Throwable $t) {
            if ($throwException) {
                /** @noinspection PhpUnhandledExceptionInspection */
                throw  $t;
            }

            return null;
        }
    }


    public static function getFileListParameterBaseStatically(
        ?string $actionName = 'upload',
        ?bool $throwException = true,
        ?array $config = null
    ): ?string {

        $config['actionConfigDetection'] = true;

        /** @noinspection PhpUnhandledExceptionInspection */
        $controller = static::create(null, null, $config, $throwException);

        /** @noinspection PhpUnhandledExceptionInspection */
        return $controller->getFileListParameterBase($actionName ?? 'upload', $throwException ?? true);
    }


    public static function getFileListParameterNameStatically(
        ?string $actionName = 'upload',
        ?bool $throwException = true,
        ?array $config = null
    ): ?string {

        $config['actionConfigDetection'] = true;

        /** @noinspection PhpUnhandledExceptionInspection */
        $controller = static::create(null, null, $config, $throwException);

        /** @noinspection PhpUnhandledExceptionInspection */
        return $controller->getFileListParameterName($actionName ?? 'upload', $throwException ?? true);
    }

    /**
     * @return bool
     */
    public function getActionConfigDetection(): bool
    {
        return $this->actionConfigDetection;
    }
}
