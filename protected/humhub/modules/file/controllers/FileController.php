<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\controllers;

use Yii;
use yii\web\HttpException;
use yii\web\UploadedFile;
use humhub\components\behaviors\AccessControl;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\file\actions\DownloadAction;
use humhub\modules\file\models\File;

/**
 * UploadController provides uploading functions for files
 *
 * @since 0.5
 */
class FileController extends \humhub\components\Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => AccessControl::className(),
                'guestAllowedActions' => ['download']
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'download' => [
                'class' => DownloadAction::className(),
            ],
        ];
    }

    /**
     * Action which handles file uploads
     *
     * The result is an json array of all uploaded files.
     */
    public function actionUpload()
    {
        Yii::$app->response->format = 'json';

        // Object which the uploaded file(s) belongs to (optional)
        $object = null;
        $objectModel = Yii::$app->request->get('objectModel');
        $objectId = Yii::$app->request->get('objectId');
        if ($objectModel != "" && $objectId != "" && \humhub\libs\Helpers::CheckClassType($objectModel, \yii\db\ActiveRecord::className())) {
            $givenObject = $objectModel::findOne(['id' => $objectId]);
            // Check if given object is HActiveRecordContent or HActiveRecordContentAddon and can be written by the current user
            if ($givenObject !== null && ($givenObject instanceof ContentActiveRecord || $givenObject instanceof ContentAddonActiveRecord) && $givenObject->content->canWrite()) {
                $object = $givenObject;
            }
        }

        $files = array();
        foreach (UploadedFile::getInstancesByName('files') as $cFile) {
            $files[] = $this->handleFileUpload($cFile, $object);
        }

        return ['files' => $files];
    }

    /**
     * Handles a single upload by given CUploadedFile and returns an array
     * of informations.
     *
     * The 'error' attribute of the array, indicates there was an error.
     *
     * Informations on error:
     *       - error: true
     *       - errorMessage: some message
     *       - name: name of the file
     *       - size: file size
     *
     * Informations on success:
     *      - error: false
     *      - name: name of the uploaded file
     *      - size: file size
     *      - guid: of the file
     *      - url: url to the file
     *      - thumbnailUrl: url to the thumbnail if exists
     *
     * @param type $cFile
     * @return Array Informations about the uploaded file
     */
    protected function handleFileUpload($cFile, $object = null)
    {
        $output = array();

        $file = new File();
        $file->setUploadedFile($cFile);

        if ($object != null) {
            $file->object_id = $object->getPrimaryKey();
            $file->object_model = $object->className();
        }

        if ($file->validate() && $file->save()) {
            $output['error'] = false;
            $output['guid'] = $file->guid;
            $output['name'] = $file->file_name;
            $output['title'] = $file->title;
            $output['size'] = $file->size;
            $output['mimeIcon'] = \humhub\libs\MimeHelper::getMimeIconClassByExtension($file->getExtension());
            $output['mimeType'] = $file->mime_type;
            $output['url'] = $file->getUrl("", false);

            $previewImage = new \humhub\modules\file\converter\PreviewImage();
            $previewImage->applyFile($file);
            $output['thumbnailUrl'] = $previewImage->getUrl();
        } else {
            $output['error'] = true;
            $output['errors'] = $file->getErrors();
        }

        $output['name'] = $file->file_name;
        $output['size'] = $file->size;
        $output['deleteUrl'] = "";
        $output['deleteType'] = "";
        $output['thumbnailUrl'] = "";

        return $output;
    }

    public function actionDelete()
    {
        $this->forcePostRequest();

        $guid = Yii::$app->request->post('guid');
        $file = File::findOne(['guid' => $guid]);

        if ($file == null) {
            throw new HttpException(404, Yii::t('FileModule.controllers_FileController', 'Could not find requested file!'));
        }

        if (!$file->canDelete()) {
            throw new HttpException(401, Yii::t('FileModule.controllers_FileController', 'Insufficient permissions!'));
        }

        $file->delete();
    }

}
