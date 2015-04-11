<div class="panel panel-default">
    <div class="panel-body" id="contentFormBody">

        <?php echo CHtml::form('', 'POST'); ?>

        <ul id="contentFormError">
        </ul>

        <?php echo $form; ?>

        <?php

        /* Modify textarea for mention input */
        //$this->widget('application.widgets.HEditorWidget', array(
        //    'id' => 'contentForm_message',
        //));

        ?>


        <?php
        echo CHtml::hiddenField("containerGuid", $contentContainer->guid);
        echo CHtml::hiddenField("containerClass", get_class($contentContainer));
        ?>
        <div class="contentForm_options">

            <hr>

            <div class="btn_container">

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
                        //currentStream.prependEntry(response.wallEntryId);

                        // Reset Form (Empty State)
                        $('.contentForm').filter(':text').val('');
                        $('.contentForm').filter('textarea').val('').trigger('autosize.resize');
                        $('.contentForm').attr('checked', false);
                        $('.userInput').remove(); // used by UserPickerWidget
                        $('.label-public').addClass('hidden');
                        $('#contentFrom_files').val('');
                        $('#contentForm_message_contenteditable').html('" . Yii::t("WallModule.widgets_views_contentForm", "Whats on your mind?") . "');
                        $('#contentForm_message_contenteditable').addClass('atwho-placeholder');



                    } else {

                        $('#contentFormError').show();

                        $.each(response.errors, function(fieldName, errorMessage){

                            // Mark Fields as Error
                            fieldId = 'contentForm_'+fieldName;
                            $('#'+fieldId).addClass('error');

                            $.each(errorMessage, function(key, msg) {
                                $('#contentFormError').append('<li><i class=\"icon-warning-sign\"></i> '+msg+'</li>');
                            });

                        });

                    }
             }",
                    ), array('id' => "post_submit_button", 'class' => 'btn btn-info')
                );
                ?>


            </div>

            <?php
            // Creates a list of already uploaded Files
            $this->widget('application.modules_core.file.widgets.FileUploadListWidget', array(
                'uploaderId' => 'contentFormFiles'
            ));
            ?>

        </div>
        <!-- /contentForm_Options -->
        <?php echo CHtml::endForm(); ?>
    </div>
    <!-- /panel body -->
</div> <!-- /panel -->

<div class="clearFloats"></div>

<script type="text/javascript">

    // Hide options by default
    //jQuery('.contentForm_options').hide();
$('#contentForm_message').focus();
    $('#contentFormError').hide();

    // Remove info text from the textinput
    jQuery('#contentFormBody').click(function () {

        // Hide options by default
        jQuery('.contentForm_options').fadeIn();

    });

    function changeVisibility() {
        if ($('#contentForm_visibility').attr('checked') != 'checked') {
            $('#contentForm_visibility').attr('checked', 'checked');
            $('#contentForm_visibility_entry').html('<i class="fa fa-lock"></i> <?php echo Yii::t('WallModule.widgets_views_contentForm', 'Make private'); ?>');
            $('.label-public').removeClass('hidden');
        } else {
            $('#contentForm_visibility').removeAttr('checked');
            $('#contentForm_visibility_entry').html('<i class="fa fa-unlock"></i> <?php echo Yii::t('WallModule.widgets_views_contentForm', 'Make public'); ?>');
            $('.label-public').addClass('hidden');
        }
    }


    // set the size for one row (Firefox)
    $('textarea').css({height: '72px', width: '100%'});

    // add autosize function to input
    //$('.autosize').autosize();

</script>
