<div class="panel panel-default post-form">
    <div class="panel-body">
        <?php
        $defaultText = Yii::t('PostModule.base', "Whats on your mind?");

        echo CHtml::form('', 'post');
        echo CHtml::textArea("message", '', array('id' => "postFrom_messageField", 'class' => 'form-control autosize', 'rows' => '1', 'placeholder' => Yii::t("PostModule.base", "Whats on your mind?")));
        // Hidden Field where FileUploadButton Widget can set files
        echo CHtml::hiddenField("fileList", '', array('id' => "postFrom_files"));
        ?>


        <div id="postForm_options">
            <hr>
            <?php
            $url = CHtml::normalizeUrl(Yii::app()->createUrl('post/post/post', array('guid' => $guid, 'target' => $target)));
            echo HHtml::ajaxSubmitButton(Yii::t('base', 'Post'), $url, array(
                    'type' => 'POST',
                    'dataType' => 'json',
                    'success' => "function(response) {

                if (!response.error) {
                    // application.modules_core.wall function
                    currentStream.prependEntry(response.wallEntryId);

                    // Clear Form
                    $('#postFrom_messageField').val('');
                    $('#postFrom_messageField').css('height', '30px');
                    $('#postFrom_files').val('');
                    $('#public').attr('checked', false);

                    // Notify FileUploadButtonWidget to clear (by providing uploaderId)
                    clearFileUpload('postFormFiles');
                } else {
                    alert(response.errorMessage);
                }
         }",
                ), array(
                    'id' => "post_submit_button",
                    'class' => 'btn btn-info'
                )
            );
            ?>
            <?php
            // Creates Uploading Button
            $this->widget('application.modules_core.file.widgets.FileUploadButtonWidget', array(
                'uploaderId' => 'postFormFiles', // Unique ID of Uploader Instance
                'bindToFormFieldId' => 'postFrom_files', // Hidden field to store uploaded files
            ));
            ?>

            <!-- content sharing -->
            <div class="pull-right">
                <?php if ($canShare): ?>
                    <div class="checkbox">
                        <label>
                            <?php echo CHtml::checkbox("public", "", array()); ?> <?php echo Yii::t('PostModule.base', 'This post is public'); ?>
                        </label>
                    </div>

                <?php endif; ?>
            </div>
            <?php echo CHtml::endForm(); ?>

            <?php
            // Creates a list of already uploaded Files
            $this->widget('application.modules_core.file.widgets.FileUploadListWidget', array(
                'uploaderId' => 'postFormFiles', // Unique ID of Uploader Instance
                'bindToFormFieldId' => 'postFrom_files', // Hidden field to store uploaded files
            ));
            ?>

        </div>
    </div>
</div>


<script type="text/javascript">

    // Hide options by default
    jQuery('#postForm_options').hide();

    // Remove info text from the textinput
    jQuery('#postFrom_messageField').click(function () {

        // Hide options by default
        jQuery('#postForm_options').fadeIn();

    });

    // add autosize function to input
    $('.autosize').autosize();

</script>
