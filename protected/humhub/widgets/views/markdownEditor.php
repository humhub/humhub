<?php
/**
 * Create a hidden field to store uploaded files guids
 */
echo CHtml::hiddenField('fileUploaderHiddenGuidField', "", array('id' => 'fileUploaderHiddenGuidField_' . $fieldId));
?>

<?php
/**
 * We need to use this script part since a markdown editor can also included
 * into a modal. So we need to append MarkdownEditors modals later to body.
 */
?>
<script id="markdownEditor_dialogs_<?php echo $fieldId; ?>" type="text/placeholder">
<div class="modal" id="addFileModal_<?php echo $fieldId; ?>" tabindex="-1" role="dialog" aria-labelledby="addImageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="addImageModalLabel"><?php echo Yii::t('widgets_views_markdownEditor', 'Add image/file'); ?></h4>
            </div>
            <div class="modal-body">

                <div class="uploadForm">
                    <?php echo CHtml::beginForm('', 'post'); ?>
                    <input class="fileUploadButton" type="file"
                           name="files[]"
                           data-url="<?php echo Yii::app()->createUrl('//file/file/upload', array()); ?>"
                           multiple>
                           <?php echo CHtml::endForm(); ?>
                </div>

                <div class="uploadProgress">
                    <strong><?php echo Yii::t('widgets_views_markdownEditor', 'Please wait while uploading...'); ?></strong>
                </div>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Yii::t('widgets_views_markdownEditor', 'Close'); ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="addLinkModal_<?php echo $fieldId; ?>" tabindex="-1" role="dialog" aria-labelledby="addLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="addLinkModalLabel"><?php echo Yii::t('widgets_views_markdownEditor', 'Add link'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="addLinkTitle"><?php echo Yii::t('widgets_views_markdownEditor', 'Title'); ?></label>
                    <input type="text" class="form-control linkTitle" 
                           placeholder="<?php echo Yii::t('widgets_views_markdownEditor', 'Title of your link'); ?>">
                </div>
                <div class="form-group">
                    <label for="addLinkTarget"><?php echo Yii::t('widgets_views_markdownEditor', 'Target'); ?></label>
                    <input type="text" class="form-control linkTarget"
                           placeholder="<?php echo Yii::t('widgets_views_markdownEditor', 'Enter a url (e.g. http://example.com)'); ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Yii::t('widgets_views_markdownEditor', 'Close'); ?></button>
                <button type="button" class="btn btn-primary addLinkButton"><?php echo Yii::t('widgets_views_markdownEditor', 'Add link'); ?></button>
            </div>
        </div>
    </div>
</div>
</script>
