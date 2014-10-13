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
class FileUploadButtonWidget extends HWidget
{

    /**
     * @var String unique id of this uploader
     */
    public $uploaderId = "";

    /**
     * Hidden field which stores uploaded file guids
     * 
     * @var string
     */
    public $fileListFieldName = '';

    /**
     * The HActiveRecord which the uploaded files belongs to.
     * Leave empty when object not exists yet.
     * 
     * @var HActiveRecord
     */
    public $object = null;

    /**
     * Ensure that imported javascript resources are included in the output
     */
    public function init()
    {
        $assetPrefix = Yii::app()->assetManager->publish(dirname(__FILE__) . '/../resources', true, 0, defined('YII_DEBUG'));
        Yii::app()->clientScript->registerScriptFile($assetPrefix . '/fileuploader.js');
        Yii::app()->clientScript->setJavascriptVariable('fileuploader_error_modal_title', Yii::t('FileModule.widgets_FileUploadButtonWidget', '<strong>Upload</strong> error'));
        Yii::app()->clientScript->setJavascriptVariable('fileuploader_error_modal_btn_close', Yii::t('FileModule.widgets_FileUploadButtonWidget', 'Close'));
        Yii::app()->clientScript->setJavascriptVariable('fileuploader_error_modal_errormsg', Yii::t('FileModule.widgets_FileUploadButtonWidget', 'Could not upload File:'));
    }

    /**
     * Draws the Upload Button output.
     */
    public function run()
    {
        $objectModel = "";
        $objectId = "";
        if ($this->object !== null) {
            $objectModel = get_class($this->object);
            $objectId = $this->object->getPrimaryKey();
        }

        $this->render('fileUploadButton', array(
            'fileListFieldName' => $this->fileListFieldName,
            'uploaderId' => $this->uploaderId,
            'objectModel' => $objectModel,
            'objectId' => $objectId
        ));
    }

}

?>