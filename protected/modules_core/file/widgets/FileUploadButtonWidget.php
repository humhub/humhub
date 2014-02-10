<?php

/**
 * FileUploadButtonWidget creates an upload button / system.
 *
 * The button uploads files and stores the uploaded file guids to a given hidden field id.
 * The underlying module can use the guids to adobt these files.
 *
 * The related widget FileUploadListWidget can optionally used to display states
 * of the current upload progress.
 *
 * @package humhub.modules_core.file.widgets
 * @since 0.5
 */
class FileUploadButtonWidget extends HWidget {

    /**
     * @var String unique id of this uploader
     */
    public $uploaderId = "fileUploader";

    /**
     * @var String Hidden Form Field where to attach the uploaded file guids (e.g. guid1, guid2, guid3)
     */
    public $bindToFormFieldId = "fileUploader";

    /**
     * Ensure that imported javascript resources are included in the output
     */
    public function init() {
        $assetPrefix = Yii::app()->assetManager->publish(dirname(__FILE__) . '/../resources', true, 0, defined('YII_DEBUG'));
        Yii::app()->clientScript->registerScriptFile($assetPrefix . '/jquery.ui.widget.js');
        Yii::app()->clientScript->registerScriptFile($assetPrefix . '/jquery.iframe-transport.js');
        Yii::app()->clientScript->registerScriptFile($assetPrefix . '/jquery.fileupload.js');
    }

    /**
     * Draws the Upload Button output.
     */
    public function run() {
        $this->render('fileUploadButton', array(
            'uploaderId' => $this->uploaderId,
            'bindToFormFieldId' => $this->bindToFormFieldId,
        ));
    }

}

?>