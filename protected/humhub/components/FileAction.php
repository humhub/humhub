<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\libs\UUID;
use humhub\modules\file\libs\FileControllerInterface;
use humhub\modules\file\models\File;
use humhub\modules\file\models\FileInterface;
use humhub\modules\file\models\forms\FileUpload;
use humhub\modules\file\models\forms\FileUploadInterface;
use yii\base\InvalidConfigException;
use yii\web\UploadedFile;

class FileAction extends ActiveRecordAction
{
    public const EVENT_AFTER_LOAD_FILE = 'after-load-file';
    public const EVENT_BEFORE_LOAD_FILE = 'before-load-file';
    public const EVENT_NO_FILE_FOUND = 'mo-file-found';

    /**
     * @var File|string the file model (you may want to overwrite this for own validations)
     */
    protected string $fileClass = File::class;



    /**
     * @return FileInterface|File|null
     * @throws InvalidConfigException
     * @see UUID::is_valid()
     */
    protected function loadFile(): ?FileInterface
    {
        $result = $this->filterValue(
            self::EVENT_BEFORE_LOAD_FILE,
            null,
            [FileInterface::class, 'string'],
        );

        // the file was returned from the event
        if (
            $result instanceof FileInterface
            || ($this->controller instanceof FileControllerInterface
                && ($result = $this->controller->getFile($this)) instanceof FileInterface
            )
        ) {
            return $this->filterValue(self::EVENT_AFTER_LOAD_FILE, $result, [FileInterface::class]);
        }

        // use guid as default parameter name
        $result = 'guid';

        $filename = $parameter = $value = null;

        while (!is_array($result)) {
            if (UUID::is_valid($result)) {
                $result = ['guid' => $result];
                break;
            }

            if ($value) {
                // ok, we were not able to do anything meaningful with that value, so put it back
                $this->setGet($parameter, $value);
                $value = null;
            }

            if (!is_string($result)) {
                break;
            }

            $filename ??= $uploadedFile
                ? $uploadedFile->getBaseName()
                : '';

            if ($result && $value = $this->getGetPost($result)) {
                $parameter = $result;

                if (is_array($value)) {
                    if ($uploadedFile) {
                        $search = [];

                        if ('' !== $extension = $uploadedFile->getExtension()) {
                            $search[] = $filename . '.' . $extension;
                        }

                        $search[] = $filename;

                        foreach ($search as $key) {
                            if ($result = $value[$key] ?? null) {
                                unset($value[$key]);
                                $this->setGet($parameter, $value);
                                $value = null;
                                continue 2;
                            }
                        }
                    }

                    // use the array as search criteria
                    $result = $value;
                    break;
                }

                $result = $value;
                continue;
            }

            switch ($parameter) {
                case 'guid':
                    if (!$filename) {
                        break 2;
                    }
                    // retry with $filename
                    $result = $filename;
                    continue 2;

                case $filename:
                    // we run out of options
                    break 2;

                default:
                    // retry with "guid"
                    $result = 'guid';
                    continue 2;
            }
        }

        if (!empty($result)) {
            $file = $this->findOne($result);

            if ($file === null && $value) {
                $this->setGet($parameter, $value);
            }
        } else {
            $file = null;
        }

        while (!$file instanceof FileUploadInterface) {
            $result = $this->filterValue(
                self::EVENT_NO_FILE_FOUND,
                $uploadedFile,
                [UploadedFile::class, FileUploadInterface::class, $this->fileClass, 'string']
            );

            if ($result instanceof FileUploadInterface) {
                return $this->filterValue(self::EVENT_AFTER_LOAD_FILE, $result, FileUploadInterface::class);
            }

            /** @var FileUploadInterface|FileUpload $fileUploadClass */
            $fileUploadClass = $result;

            $params = (array)$this->getGetPost(null, []);

            if ($this->record) {
                /** @noinspection PhpPossiblePolymorphicInvocationInspection */
                $criteria = array_merge($params, [
                    'object_model' => get_class($this->record),
                    'object_id' => $this->record->id,
                ]);
                $params = [];
            } else {
                $criteria = ['guid' => $this->getGetPost('guid')];
            }

            $file = $fileUploadClass::safelyFindAndLoad($criteria, $params);
            $file = $file instanceof File
                ? $file
                : $fileUploadClass::safelyCreateAndLoad([], array_merge($params, $criteria));
        }

        return $this->filterValue(self::EVENT_AFTER_LOAD_FILE, $result, FileUploadInterface::class);
    }

    /**
     * @param $guid
     * @return File|FileInterface|null
     * @throws InvalidConfigException
     */
    public function findOne($guid): ?FileInterface
    {
        return $this->fileClass::findByGuid($guid);
    }

    /**
     * @return string
     */
    public function getFileClass(): string
    {
        return $this->fileClass;
    }
}
