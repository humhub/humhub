<div class="panel panel-default">
    <div class="panel-body" id="contentFormBody">
        <?php echo CHtml::form('', 'POST'); ?>

        <ul id="contentFormError">
        </ul>

        <?php echo $form; ?>

        <div id="notifyUserContainer" class="form-group hidden" style="margin-top: 15px;">
            <input type="text" value="" id="notifiyUserInput" name="notifiyUserInput"/>

            <?php

            $user_url = '//space/space/searchMemberJson';
            if (get_class($contentContainer) == Wall::TYPE_USER) {
                $user_url = '//user/search/json';
            }

            $this->widget('application.modules_core.user.widgets.UserPickerWidget', array(
                'inputId' => 'notifiyUserInput',
                'userSearchUrl' => $this->createUrl($user_url, array('sguid' => $this->contentContainer->guid, 'keyword' => '-keywordPlaceholder-')),
                'maxUsers' => 10,
                'userGuid' => Yii::app()->user->guid,
                'placeholderText' => Yii::t('WallModule.base', 'Add a member to notify'),
                'focus' => true,
            ));
            ?>
        </div>

        <?php
        echo CHtml::hiddenField("fileList", '', array('id' => "contentFrom_files"));
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
                        currentStream.prependEntry(response.wallEntryId);

                        // Reset Form (Empty State)
                        $('.contentForm').filter(':text').val('');
                        $('.contentForm').filter('textarea').val('').trigger('autosize.resize');
                        $('.contentForm').attr('checked', false);
                        $('.userInput').remove(); // used by UserPickerWidget
                        $('#notifyUserContainer').addClass('hidden');
                        $('#notifiyUserInput').val('');

                        $('#contentFrom_files').val('');
                        $('#public').attr('checked', false);

                        // Notify FileUploadButtonWidget to clear (by providing uploaderId)
                        clearFileUpload('contentFormFiles');

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
                <?php
                // Creates Uploading Button
                $this->widget('application.modules_core.file.widgets.FileUploadButtonWidget', array(
                    'uploaderId' => 'contentFormFiles', // Unique ID of Uploader Instance
                    'bindToFormFieldId' => 'contentFrom_files', // Hidden field to store uploaded files
                ));
                ?>

                <!-- content sharing -->
                <div class="pull-right">

                    <div class="checkbox hidden">
                        <label>
                            <?php echo CHtml::checkbox("visibility", "", array('id' => 'contentForm_visibility', 'class' => 'contentForm')); ?> <?php echo Yii::t('WallModule.base', 'Is public'); ?>
                        </label>
                    </div>

                    <!--                        <a class="tt btn btn-icon" href="" data-toggle="tooltip" data-placement="top" data-original-title="<?php /*echo Yii::t('WallModule.base', 'Notify related members about this post'); */ ?>"><i class="icon-bell-alt"></i></a>
                        <a class="tt btn btn-icon" href="" data-toggle="tooltip" data-placement="top" data-original-title="<?php /*echo Yii::t('WallModule.base', 'Make public for nonmembers<br>and followers of this space'); */ ?>"><i class="icon-lock"></i></a>
-->
                    <span class="label label-success label-public hidden">Public</span>

                    <ul class="nav nav-pills preferences">
                        <li class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="icon-cogs"></i></a>
                            <ul class="dropdown-menu pull-right">
                                <li>
                                    <a href="javascript:notifyUser();"><i
                                            class="icon-bell-alt"></i> <?php echo Yii::t('WallModule.base', 'Notify members'); ?>
                                    </a>
                                </li>
                                <?php if (get_class($this->contentContainer) == 'Space' && $this->contentContainer->canShare()): /* can create public content */ ?>
                                    <li>
                                        <a id="contentForm_visibility_entry" href="javascript:changeVisibility();"><i
                                                class="icon-unlock"></i> <?php echo Yii::t('WallModule.base', 'Make public'); ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                    </ul>


                </div>

            </div>

            <?php echo CHtml::endForm(); ?>

            <?php
            // Creates a list of already uploaded Files
            $this->widget('application.modules_core.file.widgets.FileUploadListWidget', array(
                'uploaderId' => 'contentFormFiles', // Unique ID of Uploader Instance
                'bindToFormFieldId' => 'contentFrom_files', // Hidden field to store uploaded files
            ));
            ?>

        </div>
        <!-- /contentForm_Options -->
    </div>
    <!-- /panel body -->
</div> <!-- /panel -->

<div class="clearFloats"></div>

<script type="text/javascript">

    // Hide options by default
    jQuery('.contentForm_options').hide();
    $('#contentFormError').hide();


    // Remove info text from the textinput
    jQuery('#contentFormBody').click(function () {

        // Hide options by default
        jQuery('.contentForm_options').fadeIn();

    });

    function changeVisibility() {
        if ($('#contentForm_visibility').attr('checked') != 'checked') {
            $('#contentForm_visibility').attr('checked', 'checked');
            $('#contentForm_visibility_entry').html('<i class="icon-lock"></i> <?php echo Yii::t('WallModule.base', 'Make private'); ?>');
            $('.label-public').removeClass('hidden');
        } else {
            $('#contentForm_visibility').removeAttr('checked');
            $('#contentForm_visibility_entry').html('<i class="icon-unlock"></i> <?php echo Yii::t('WallModule.base', 'Make public'); ?>');
            $('.label-public').addClass('hidden');
        }
    }

    function notifyUser() {
        $('#notifyUserContainer').removeClass('hidden');
        $('#notifiyUserInput_tag_input_field').focus();
    }

    // add autosize function to input
    $('.autosize').autosize();

</script>