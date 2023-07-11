<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\actions;

use Exception;
use humhub\components\EventWithTypedResult;
use humhub\components\EventWithUploadedFile;
use humhub\components\FileAction;
use humhub\exceptions\InvalidConfigTypeException;
use humhub\libs\Html;
use humhub\libs\ObjectModel;
use humhub\modules\file\libs\FileControllerInterface;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\models\File;
use humhub\modules\file\models\forms\FileUpload;
use humhub\modules\file\models\forms\FileUploadInterface;
use Throwable;
use Yii;
use yii\base\ModelEvent;
use yii\db\AfterSaveEvent;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * UploadAction provides an Ajax/JSON way to upload new files
 *
 * @since 1.2
 * @author Luke
 */
class UploadAction extends FileAction
{
    /**
     * @var string|UploadedFile
     */
    public string $uploadedFileClass = UploadedFile::class;

    /**
     * @var FileUploadInterface|FileUpload|string the file model (you may want to overwrite this for own validations)
     */
    protected string $fileClass = FileUpload::class;

    /**
     * @var string|null scenario for file upload validation
     */
    protected ?string $scenario = null;

    /**
     * @var string
     */
    public string $uploadName = 'files';

    /**
     * @inheritdoc
     * @throws Throwable
     */
    public function init()
    {
        $this->on(self::EVENT_BEFORE_LOAD_RECORD, [$this, 'eventHandlerOnBeforeLoadRecord']);

        $actionConfigDetection = parent::init();

        if (!$actionConfigDetection) {
            Yii::$app->response->format = 'json';
            if (!is_a($this->uploadedFileClass, UploadedFile::class, true)) {
                throw new  InvalidConfigTypeException(
                    __METHOD__,
                    '$uploadedFileClass',
                    UploadedFile::class,
                    $this->uploadedFileClass
                );
            }
        }
    }

    /**
     * @noinspection PhpMissingParamTypeInspection
     */
    public function run()
    {
        $files = $this->uploadedFileClass::getInstancesByName($this->uploadName);
        $hideInStream = $this->isHideInStreamRequest();

        array_walk(
            $files,
            /**
             * @param UploadedFile $uploadedFile
             *
             * @return void
             */
            function (
                &$uploadedFile
            ) use (
                $hideInStream
            ) {
                $uploadedFile = $this->handleFileUpload($uploadedFile, $hideInStream);
            }
        );

        return ['files' => $files];
    }

    public function eventHandlerOnBeforeLoadRecord(EventWithTypedResult $e)
    {
        if ($e->getResult() === null && ($file = $this->loadFile(null)) && ($model = $file->object_model) && ($pk = $file->object_id)) {
            $e->setResult(new ObjectModel($model, $pk));
        }
    }

    public function eventHandlerOnBeforeSave(ModelEvent $e)
    {
    }

    public function eventHandlerOnAfterSave(AfterSaveEvent $e)
    {
        /** @var FileUploadInterface $fileUpdated */
        $fileUpdated = $e->sender;
    }

    /**
     * Handles the file upload for are particular UploadedFile
     */
    protected function handleFileUpload(UploadedFile $uploadedFile, $hideInStream = false): array
    {
        try {
            /* @var $file File|null */
            $file = $this->loadFile($uploadedFile);

            if (!$file instanceof FileUploadInterface) {
                /**
                 * @var $file FileUploadInterface
                 */
                $file = $this->fileClass::safelyCreateAndLoad([$file], $this->getGet(), '');
            } elseif ($this->scenario !== null) {
                $file->scenario = $this->scenario;
            }

            $file->setUploadedFile($uploadedFile);

            if ($hideInStream) {
                $file->show_in_stream = false;
            }

            $file->on(BaseActiveRecord::EVENT_BEFORE_INSERT, [$this, 'eventHandlerOnBeforeSave']);
            $file->on(BaseActiveRecord::EVENT_BEFORE_UPDATE, [$this, 'eventHandlerOnBeforeSave']);
            $file->on(BaseActiveRecord::EVENT_AFTER_INSERT, [$this, 'eventHandlerOnAfterSave']);
            $file->on(BaseActiveRecord::EVENT_AFTER_UPDATE, [$this, 'eventHandlerOnAfterSave']);

            if ($file->save()) {
                if ($this->autoAttach && $this->record !== null && !$this->record->isNewRecord) {
                    $this->record->fileManager->attach($file);
                }

                return $this->afterFileUpload($file) ?? array_merge(
                    ['error' => false],
                    FileHelper::getFileInfos($file)
                );
            }

            return $this->getErrorResponse($file);
        } catch (Exception $e) {
            return [
                'name' => $file->file_name ?? null,
                'error' => true,
                'errors' => [$e->getMessage()],
            ];
        }
    }

    protected function isHideInStreamRequest(): bool
    {
        return filter_var($this->getPost('hideInStream'), FILTER_VALIDATE_BOOLEAN) || filter_var(
            $this->getGet('hideInStream'),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    /**
     * Is called after a file has been successfully uploaded and saved.
     *
     * @param FileUploadInterface|FileUpload $file
     *
     * @return array|null
     * @since 1.7
     */
    protected function afterFileUpload(FileUploadInterface $file): ?array
    {
        $variant = $file->updateMode === File::UPDATE_REPLACE
            ? null
            : '_draft';

        return ArrayHelper::merge(
            FileHelper::getFileInfos($file, $variant),
            [
                'error' => false,
            ]
        );
    }

    protected function loadFile(?UploadedFile $uploadedFile = null): ?FileUploadInterface
    {
        $result = $this->filterValue(
            self::EVENT_BEFORE_LOAD_FILE,
            EventWithUploadedFile::create()->setUploadedFile($uploadedFile),
            [FileUploadInterface::class, 'string'],
        );

        // the file was returned from the event
        if (
            $result instanceof FileUploadInterface // the file has already been processed
            || ($uploadedFile && !file_exists($uploadedFile->tempName)) // try to get the file form the controller
            || ($this->controller instanceof FileControllerInterface
                && ($result = $this->controller->getFile($this)) instanceof FileUploadInterface
            )
        ) {
            return $this->filterValue(self::EVENT_AFTER_LOAD_FILE, $result, [FileUploadInterface::class]);
        }

        return parent::loadFile($uploadedFile);
    }

    /**
     * Returns the error response for a file upload as array
     *
     * @param File $file
     *
     * @return array the upload error information
     */
    protected function getErrorResponse(File $file): array
    {
        $errorMessage = Yii::t(
            'FileModule.base',
            'File {fileName} could not be uploaded!',
            ['fileName' => Html::encode($file->file_name)]
        );

        if ($file->getErrors()) {
            $errorMessage = $file->getErrors('uploadedFile');
        }

        return [
            'error' => true,
            'errors' => $errorMessage,
            'name' => Html::encode($file->file_name),
            'size' => Html::encode($file->size),
        ];
    }
}
