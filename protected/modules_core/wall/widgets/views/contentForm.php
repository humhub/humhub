<div class="panel panel-default">
    <div class="panel-body" id="contentFormBody">
        <?php echo CHtml::form('', 'POST'); ?>

        <ul id="contentFormError">
        </ul>

        <?php echo $form; ?>

        <?php
        echo CHtml::hiddenField("fileList", '', array('id' => "contentFrom_files"));
        echo CHtml::hiddenField("containerGuid", $contentContainer->guid);
        echo CHtml::hiddenField("containerClass", get_class($contentContainer));
        ?>

        <div class="contentForm_options">

            <hr>

            <?php
            $url = CHtml::normalizeUrl(Yii::app()->createUrl($submitUrl));
            echo HHtml::ajaxSubmitButton($submitButtonText, $url, array(
                'type' => 'POST',
                'dataType' => 'json',
                'beforeSend' => "function() {
                    $('.contentForm').removeClass('error');
                    $('#contentFormError').hide();
                    $('#contentFormError').empty();
                }",
                'success' => "function(response) {
                    if (response.success) {
                    
                        // application.modules_core.wall function
                        currentStream.prependEntry(response.wallEntryId);

                        // Reset Form (Empty State)
                        $('.contentForm').filter(':text').val('');
                        $('.contentForm').filter('textarea').val('');
                        $('.contentForm').attr('checked', false); 
                        $('.userInput').remove(); // used by UserPickerWidget

                        $('#contentFrom_files').val('');
                        $('#public').attr('checked', false);

                        // Notify FileUploadButtonWidget to clear (by providing uploaderId)
                        clearFileUpload('contentFormFiles');

                    } else {

                        console.log(response);

                        $('#contentFormError').show();

                        $.each(response.errors, function(fieldName, errorMessage){
                            
                            // Mark Fields as Error
                            fieldId = 'contentForm_'+fieldName;
                            $('#'+fieldId).addClass('error');

                            $.each(errorMessage, function(key, msg) {
                                $('#contentFormError').append('<li>'+msg+'</li>');
                            });
                            
                        });

                    }
             }",
                    ), array('id' => "post_submit_button", 'class' => 'btn btn-info')
            );
            ?>
            <?php
            // Creates Uploading Button
            $this->widget('application.modules_core.file.widgets.FileUploadButtonWidget', array(
                'uploaderId' => 'contentFormFiles', // Unique ID of Uploader Instance
                'bindToFormFieldId' => 'contentFrom_files', // Hidden field to store uploaded files
            ));
            ?>

            <!-- content sharing -->
            <div class="pull-right">
                <?php if (get_class($this->contentContainer) == 'Space' && $this->contentContainer->canShare()): /* can create public content */ ?>
                    <div class="checkbox">
                        <label>
                            <?php echo CHtml::checkbox("visibility", "", array('id'=>'contentForm_visibility', 'class' => 'contentForm')); ?> <?php echo Yii::t('WallModule.base', 'Is public'); ?>
                        </label>
                    </div>

                <?php endif; ?>
            </div>

            <?php echo CHtml::endForm(); ?>

            <?php
            // Creates a list of already uploaded Files
            $this->widget('application.modules_core.file.widgets.FileUploadListWidget', array(
                'uploaderId' => 'contentFormFiles', // Unique ID of Uploader Instance
                'bindToFormFieldId' => 'contentFrom_files', // Hidden field to store uploaded files
            ));
            ?>

        </div> <!-- /contentForm_Options -->
    </div> <!-- /panel body -->
</div> <!-- /panel -->

<div class="clearFloats"></div>

<script type="text/javascript">

    // Hide options by default
    jQuery('.contentForm_options').hide();
    $('#contentFormError').hide();


    // Remove info text from the textinput
    jQuery('#contentFormBody').click(function() {

        // Hide options by default
        jQuery('.contentForm_options').fadeIn();

    });

    // add autosize function to input
    $('.autosize').autosize();

</script>