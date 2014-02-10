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
    <br>
    <div class="progress progress-info active" id="<?php echo $uploaderId; ?>_progress" >
      <div class="bar" style="width: 0%;"></div>
    </div>
    <?php //echo Yii::t('FileModule.base', 'List of already uploaded files:'); ?>
    <ul style="list-style: none; margin: 0;" id="<?php echo $uploaderId; ?>_list">
    </ul>
</div>