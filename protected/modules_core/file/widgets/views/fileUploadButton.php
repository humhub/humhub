<?php
/**
 * Shows the upload file button which handles file uploads.
 * This view is used by FileUploadButtonWidget.
 *
 * If an FileUploadListWidget Instance is exists, this view should update some
 * informations like process or already uploaded files per javascript.
 *
 * Its also necessary to update the bindToFormFieldId on successful uploads.
 * This hidden field contains a list all uploaded file guids.
 *
 * @property String $uploaderId is the unique id of the uploader.
 * @property String $bindToFormFieldId is the id of the hidden id which stores a comma seprated list of file guids.
 *
 * @package humhub.modules_core.file.widgets
 * @since 0.5
 */
?>

<?php echo CHtml::hiddenField($this->fileListFieldName, '', array('id' => "fileUploaderHiddenField_" . $uploaderId)); ?>

<style>
    .fileinput-button {
        position: relative;
        overflow: hidden;
    }

    .fileinput-button input {
        position: absolute;
        top: 0;
        right: 0;
        margin: 0;
        opacity: 0;
        filter: alpha(opacity=0);
        transform: translate(-300px, 0) scale(4);
        font-size: 23px;
        direction: ltr;
        cursor: pointer;
    }
</style>
<span class="btn btn-info fileinput-button tt" data-toggle="tooltip" data-placement="bottom" title=""
      data-original-title="<?php echo Yii::t('FileModule.widgets_views_fileUploadButton', 'Upload files'); ?>">
    <i class="fa fa-cloud-upload"></i>

    <input id="fileUploaderButton_<?php echo $uploaderId; ?>" type="file" name="files[]"
           data-url="<?php echo Yii::app()->createUrl('//file/file/upload', array('objectModel' => $objectModel, 'objectId' => $objectId)); ?>" multiple>
</span>

<script>
    $(function() {
        'use strict';
        installUploader("<?php echo $uploaderId; ?>");

        // fixing staying tooltip while opening file browser window
        $('.fileinput-button').click(function() {
            $('.tt').tooltip('hide');
        })
    })

</script>
