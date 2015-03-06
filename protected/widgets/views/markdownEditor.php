<?php
/**
 * Create a hidden field to store uploaded files guids
 */
echo CHtml::hiddenField('fileUploaderHiddenGuidField', "", array('id' => 'fileUploaderHiddenGuidField'));
?>


<div class="modal fade" id="addImageModal" tabindex="-1" role="dialog"
     aria-labelledby="addImageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="addImageModalLabel"><?php echo Yii::t('widgets_views_markdownEditor', 'Add image/file'); ?></h4>
            </div>
            <div class="modal-body">

                <div id="addImageModalUploadForm">
                    <input id="fileUploaderButton" type="file"
                           name="files[]"
                           data-url="<?php echo Yii::app()->createUrl('//file/file/upload', array()); ?>"
                           multiple>
                </div>

                <div id="addImageModalProgress">
                    <strong><?php echo Yii::t('widgets_views_markdownEditor', 'Please wait while uploading...'); ?></strong>
                </div>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addLinkModal" tabindex="-1" role="dialog"
     aria-labelledby="addLinkModalLabel" aria-hidden="true">
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
                    <input type="text" class="form-control" id="addLinkTitle"
                           placeholder="Title of your link">
                </div>
                <div class="form-group">
                    <label for="addLinkTarget"><?php echo Yii::t('widgets_views_markdownEditor', 'Target'); ?></label>
                    <input type="text" class="form-control" id="addLinkTarget"
                           placeholder="<?php echo Yii::t('widgets_views_markdownEditor', 'Enter a url (e.g. http://example.com)'); ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Yii::t('widgets_views_markdownEditor', 'Close'); ?></button>
                <button type="button" id="addLinkButton" class="btn btn-primary"><?php echo Yii::t('widgets_views_markdownEditor', 'Add link'); ?></button>
            </div>
        </div>
    </div>
</div>