<?php

/**
 * FileUploadListWidget works in combination of FileUploadButtonWidget and is
 * primary responsible to display some status informations like upload progress
 * or a list of already uploaded files.
 *
 * This widget cannot work standalone! Make sure the attribute "uploaderId" is
 * the same as the corresponding FileUploadListWidget.
 *
 * @package humhub.modules_core.file.widgets
 * @since 0.5
 */
class FileUploadListWidget extends HWidget {

    /**
     * @var String unique id of this uploader
     */
    public $uploaderId = "fileUploader";

    /**
     * @var String Hidden Form Field where to attach the uploaded file guids (e.g. guid1, guid2, guid3)
     */
    public $bindToFormFieldId = "fileUploader";

    /**
     * Draw the widget
     */
    public function run() {
        $this->render('fileUploadList', array(
            'uploaderId' => $this->uploaderId,
            'bindToFormFieldId' => $this->bindToFormFieldId,
        ));
    }

}

?>
