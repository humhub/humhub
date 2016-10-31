<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\actions;

use Yii;
use yii\base\Action;
use yii\web\UploadedFile;
use humhub\libs\Helpers;
use humhub\libs\MimeHelper;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\models\File;
use humhub\modules\file\converter\PreviewImage;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;

/**
 * UploadAction provides an Ajax/JSON way to upload new files
 *
 * @since 1.2
 * @author Luke
 */
class UploadAction extends Action
{

    /**
     * The record to whom this files belongs to.
     * Optional, since "free" files can also attached to a record later.
     * 
     * @var \humhub\components\ActiveRecord the records 
     */
    public $record = null;

    /**
     * @var string the file model (you may want to overwrite this for own validations)
     */
    protected $fileClass = 'humhub\modules\file\models\FileUpload';

    /**
     * @var string scenario for file upload validation
     */
    protected $scenario = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        Yii::$app->response->format = 'json';
        $this->loadRecord();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $files = array();
        foreach (UploadedFile::getInstancesByName('files') as $cFile) {
            $files[] = $this->handleFileUpload($cFile);
        }

        return ['files' => $files];
    }

    /**
     * Handles the file upload for are particular UploadedFile
     */
    protected function handleFileUpload(UploadedFile $uploadedFile)
    {
        $file = Yii::createObject($this->fileClass);

        if ($this->scenario !== null) {
            $file->scenario = $this->scenario;
        }

        $file->setUploadedFile($uploadedFile);

        if ($file->validate() && $file->save()) {
            if ($this->record !== null) {
                $this->record->fileManager->attach($file);
            }
            return $this->getSuccessResponse($file);
        } else {
            return $this->getErrorResponse($file);
        }
    }

    /**
     * Loads the target record by request parameter if defined.
     * The default implementation only supports uploads to ContentActiveRecord or ContentAddonActiveRecords.
     */
    protected function loadRecord()
    {
        $model = Yii::$app->request->get('objectModel');
        $pk = Yii::$app->request->get('objectId');

        if ($model != '' && $pk != '' && Helpers::CheckClassType($model, \yii\db\ActiveRecord::className())) {

            $record = $model::findOne(['id' => $pk]);
            if ($record !== null && ($record instanceof ContentActiveRecord || $record instanceof ContentAddonActiveRecord)) {
                if ($record->content->canWrite()) {
                    $this->record = $record;
                }
            }
        }
    }

    /**
     * Returns the success response for a file upload as array
     * 
     * @param File $file
     * @return array the basic file informations
     */
    protected function getSuccessResponse(File $file)
    {
        $thumbnailUrl = '';
        $previewImage = new PreviewImage();
        if ($previewImage->applyFile($file)) {
            $thumbnailUrl = $previewImage->getUrl();
        }

        return [
            'error' => false,
            'name' => $file->file_name,
            'guid' => $file->guid,
            'size' => $file->size,
            'mimeType' => $file->mime_type,
            'mimeIcon' => MimeHelper::getMimeIconClassByExtension(FileHelper::getExtension($file->file_name)),
            'size' => $file->size,
            'url' => $file->getUrl(),
            'thumbnailUrl' => $thumbnailUrl,
        ];
    }

    /**
     * Returns the error response for a file upload as array
     * 
     * @param File $file
     * @return array the upload error information
     */
    protected function getErrorResponse(File $file)
    {
        return [
            'error' => true,
            'errors' => $file->getErrors(),
            'name' => $file->file_name,
            'size' => $file->size
        ];
    }

}
