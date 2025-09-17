<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\components;

use humhub\components\ActiveRecord;
use humhub\components\behaviors\PolymorphicRelation;
use humhub\modules\comment\models\Comment;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\services\ContentSearchService;
use humhub\modules\file\models\File;
use Yii;
use yii\base\Component;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

/**
 * FileManager
 *
 * @todo Add caching
 * @since 1.2
 * @author Luke
 */
class FileManager extends Component
{
    /**
     * @var ActiveRecord
     */
    public $record;

    /**
     * Attach files to record.
     * This is required when uploaded before the related content is saved.
     *
     * @param string|array|File $files of File records or comma separeted list of file guids or single File record
     * @param bool $steal steal when already assigned to other record
     */
    public function attach($files, $steal = false)
    {
        if (!$files) {
            return;
        }

        if (is_string($files)) {
            $files = array_map('trim', explode(',', $files));
        } elseif ($files instanceof File) {
            $files = [$files];
        }

        foreach ($files as $file) {
            if (is_string($file) && $file != '') {
                $file = File::findOne(['guid' => $file]);
            }

            if ($file === null || !$file instanceof File) {
                continue;
            }

            if ($file->isAssignedTo($this->record)) {
                continue;
            }

            if ($file->isAssigned() && !$steal) {
                Yii::warning('Attempted to steal file: ' . $file->guid);
                continue;
            }

            $attributes = [
                'object_model' => PolymorphicRelation::getObjectModel($this->record),
                'object_id' => $this->record->getPrimaryKey(),
            ];

            if ($this->record instanceof ContentActiveRecord || $this->record instanceof Comment) {
                $attributes['content_id'] = $this->record->content->id;
            }

            $file->updateAttributes($attributes);
        }

        if ($this->record instanceof ContentActiveRecord) {
            (new ContentSearchService($this->record->content))->update();
        }

    }

    /**
     * File find query
     *
     * @return ActiveQuery file find query
     */
    public function find()
    {
        return File::find()->andWhere(['object_id' => $this->record->getPrimaryKey(), 'object_model' => PolymorphicRelation::getObjectModel($this->record)]);
    }

    /**
     * Returns a list of files assigned to the record
     *
     * @return File[] array of files assigned to the record
     */
    public function findAll()
    {
        return $this->find()->all();
    }

    /**
     * By default all files with show_in_stream set to 1.
     *
     * If $flag is set to false, this function will return all non stream files.
     *
     * @return File[]
     * @since 1.2.2
     */
    public function findStreamFiles($showInStream = true)
    {
        if ($this->record->isRelationPopulated('files')) {
            return ArrayHelper::getValue(
                ArrayHelper::index($this->record->files, null, 'show_in_stream'),
                +$showInStream,
                [],
            );
        } else {
            if ($showInStream) {
                return $this->find()->andWhere(['show_in_stream' => 1])->all();
            } else {
                return $this->find()->andWhere(['show_in_stream' => 0])->all();
            }
        }
    }

    /**
     * Returns a summary of attached files
     * @since 1.18
     */
    public function getSummary(int $fileNameMaxLength = 30, int $maxFiles = 3): string
    {
        $fileNames = [];
        $files = $this->findAll();
        foreach ($files as $file) {
            $fileNames[] = StringHelper::truncate($file->file_name, $fileNameMaxLength);
            if (count($fileNames) === $maxFiles) {
                $fileNames[] = Yii::t('FileModule.base', 'and {nbFiles} more.', ['nbFiles' => count($files) - $maxFiles]);
                break;
            }
        }
        if (!$fileNames) {
            return '';
        }
        if (count($fileNames) === 1) {
            return Yii::t('FileModule.base', 'Attachment: {file}', ['file' => $fileNames[0]]);
        }
        return Yii::t('FileModule.base', 'Attachments: {files}', ['files' => implode(', ', $fileNames)]);
    }
}
