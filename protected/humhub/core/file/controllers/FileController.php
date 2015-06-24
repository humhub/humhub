<?php

/**
 * UploadController provides uploading functions for files
 *
 * @package humhub.modules_core.file.controllers
 * @since 0.5
 */
class FileController extends Controller
{

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow',
                'users' => array((HSetting::Get('allowGuestAccess', 'authentication_internal')) ? "?" : "@"),    
                'actions' => array('download'),
                
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'users' => array('@'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Action which handles file uploads
     *
     * The result is an json array of all uploaded files.
     */
    public function actionUpload()
    {
        // Object which the uploaded file(s) belongs to (optional)
        $object = null;
        $objectModel = Yii::app()->request->getParam('objectModel');
        $objectId = Yii::app()->request->getParam('objectId');
        if ($objectModel != "" && $objectId != "") {
            $givenObject = $objectModel::model()->findByPk($objectId);
            // Check if given object is HActiveRecordContent or HActiveRecordContentAddon and can be written by the current user
            if ($givenObject !== null && ($givenObject instanceof HActiveRecordContent || $givenObject instanceof HActiveRecordContentAddon) && $givenObject->content->canWrite()) {
                $object = $givenObject;
            }
        }

        $files = array();
        foreach (CUploadedFile::getInstancesByName('files') as $cFile) {
            $files[] = $this->handleFileUpload($cFile, $object);
        }

        return $this->renderJson(array('files' => $files));
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
            $file->object_model = get_class($object);
        }

        if ($file->validate() && $file->save()) {
            $output['error'] = false;
            $output['guid'] = $file->guid;
            $output['name'] = $file->file_name;
            $output['title'] = $file->title;
            $output['size'] = $file->size;
            $output['mimeIcon'] = HHtml::getMimeIconClassByExtension($file->getExtension());
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
        $guid = Yii::app()->request->getParam('guid');
        $suffix = Yii::app()->request->getParam('suffix');

        $file = File::model()->findByAttributes(array('guid' => $guid));

        if ($file == null) {
            throw new CHttpException(404, Yii::t('FileModule.controllers_FileController', 'Could not find requested file!'));
        }

        if (!$file->canRead()) {
            throw new CHttpException(401, Yii::t('FileModule.controllers_FileController', 'Insufficient permissions!'));
        }

        $filePath = $file->getPath($suffix);
        $fileName = $file->getFilename($suffix);

        if (!file_exists($filePath . DIRECTORY_SEPARATOR . $fileName)) {
            throw new CHttpException(404, Yii::t('FileModule.controllers_FileController', 'Could not find requested file!'));
        }

        if (!HSetting::Get('useXSendfile', 'file')) {
            Yii::app()->getRequest()->sendFile($fileName, file_get_contents($filePath . DIRECTORY_SEPARATOR . $fileName), $file->mime_type);
        } else {
            $options = array(
                'saveName' => $fileName,
            );
            if (strpos($_SERVER['SERVER_SOFTWARE'], 'nginx') === 0) {
                // set nginx specific X-Sendfile header name
                $options['xHeader'] = 'X-Accel-Redirect';
                // make path relative to docroot
                $docroot = rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR);
                if (substr($filePath, 0, strlen($docroot)) == $docroot) {
                    $filePath = substr($filePath, strlen($docroot));
                }
            }
            Yii::app()->getRequest()->xSendFile($filePath . DIRECTORY_SEPARATOR . $fileName, $options);
        }
    }

    public function actionDelete()
    {
        $this->forcePostRequest();

        $guid = Yii::app()->request->getParam('guid');
        $file = File::model()->findByAttributes(array('guid' => $guid));

        if ($file == null) {
            throw new CHttpException(404, Yii::t('FileModule.controllers_FileController', 'Could not find requested file!'));
        }

        if (!$file->canDelete()) {
            throw new CHttpException(401, Yii::t('FileModule.controllers_FileController', 'Insufficient permissions!'));
        }

        $file->delete();
    }

}
