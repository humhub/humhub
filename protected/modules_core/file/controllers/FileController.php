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

        $files = array();
        foreach (CUploadedFile::getInstancesByName('files') as $cFile) {
            $files[] = $this->handleFileUpload($cFile);
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
    protected function handleFileUpload($cFile)
    {
        $output = array();

        $file = new File();
        $file->setUploadedFile($cFile);

        $output['name'] = $file->file_name;
        $output['size'] = $file->size;

        if ($file->validate() && $file->save()) {
            $output['error'] = false;
            $output['guid'] = $file->guid;
            $output['name'] = $file->file_name;
            $output['title'] = $file->title;
            $output['url'] = "";
            $output['thumbnailUrl'] = "";
            $output['size'] = $file->size;
            $output['deleteUrl'] = "";
            $output['deleteType'] = "";
            $output['mimeIcon'] = HHtml::getMimeIconClassByExtension($file->getExtension());
            ;
        } else {
            $output['error'] = true;
            $output['errors'] = $file->getErrors();
        }

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
            Yii::app()->getRequest()->xSendFile($filePath . DIRECTORY_SEPARATOR . $fileName, $options);
        }
    }

}
