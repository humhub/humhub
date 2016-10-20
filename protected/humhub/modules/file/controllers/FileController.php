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
use yii\helpers\FileHelper;

use humhub\modules\file\models\File;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;

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
                'class' => \humhub\components\behaviors\AccessControl::className(),
                'guestAllowedActions' => ['download']
            ]
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
            $output['mimeBaseType'] = $file->getMimeBaseType();
            $output['mimeSubType'] = $file->getMimeSubType();
            $output['url'] = $file->getUrl("", false);
            $output['thumbnailUrl'] = $file->getPreviewImageUrl(200, 200);
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

    /**
     * Downloads a file
     */
    public function actionDownload()
    {
        // GUID of file
        $guid = Yii::$app->request->get('guid');
        // Force Download Flag
        $download = Yii::$app->request->get('download', 0);
        // Optional suffix of file (e.g. scaled variant of image)
        $suffix = Yii::$app->request->get('suffix');

        $file = File::findOne(['guid' => $guid]);
        if ($file == null) {
            throw new HttpException(404, Yii::t('FileModule.controllers_FileController', 'Could not find requested file!'));
        }
        if (!$file->canRead()) {
            throw new HttpException(401, Yii::t('FileModule.controllers_FileController', 'Insufficient permissions!'));
        }

        if (!file_exists($file->getStoredFilePath($suffix))) {
            throw new HttpException(404, Yii::t('FileModule.controllers_FileController', 'Could not find requested file!'));
        }

        $options = [
            'inline' => false,
            'mimeType' => FileHelper::getMimeTypeByExtension($file->getFilename($suffix))
        ];

        if ($download != 1 && in_array($options['mimeType'], Yii::$app->getModule('file')->inlineMimeTypes)) {
            $options['inline'] = true;
        }

        if (!Yii::$app->getModule('file')->settings->get('useXSendfile')) {
            Yii::$app->response->sendFile($file->getStoredFilePath($suffix), $file->getFilename($suffix), $options);
        } else {
            $filePath = $file->getStoredFilePath($suffix);

            if (strpos($_SERVER['SERVER_SOFTWARE'], 'nginx') === 0) {
                // set nginx specific X-Sendfile header name
                $options['xHeader'] = 'X-Accel-Redirect';
                // make path relative to docroot
                $docroot = rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR);
                if (substr($filePath, 0, strlen($docroot)) == $docroot) {
                    $filePath = substr($filePath, strlen($docroot));
                }
            }

            Yii::$app->response->xSendFile($filePath, $file->getFilename($suffix), $options);
        }
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
