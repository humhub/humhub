<?php
/**
 * Displays a list of uploaded files by FileUploadButtonWidget.
 * The data will be updated via javascript by FileUploadButtonWidget.
 * This view is used by FileUploadListWidget.
 *
 * @property String $uploaderId is the unique id of the uploader.
 * @property String $bindToFormFieldId is the id of the hidden id which stores a comma seprated list of file guids.
 *
 * @package humhub.modules_core.file.widgets
 * @since 0.5
 */
?>
<div id="<?php echo $uploaderId; ?>_details" style="display:none">

    <div class="progress contentForm-upload-progress" id="<?php echo $uploaderId; ?>_progress" style="">
        <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="0" aria-valuemin="0"
             aria-valuemax="100" style="width: 0%">
        </div>
    </div>


    <?php //echo Yii::t('FileModule.widgets_views_fileUploadList', 'List of already uploaded files:'); ?>
    <ul style="list-style: none; display:none; margin: 0; padding-top: 10px;" id="<?php echo $uploaderId; ?>_list">
    </ul>
</div>