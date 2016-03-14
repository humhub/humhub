<?php

use yii\helpers\Html;
use yii\helpers\Url;
use humhub\modules\space\models\Space;
?>

<div class="panel panel-default">
    <div class="panel-body" id="contentFormBody">

        <?php echo Html::beginForm('', 'POST'); ?>

        <ul id="contentFormError">
        </ul>

        <?php echo $form; ?>

        <div id="notifyUserContainer" class="form-group hidden" style="margin-top: 15px;">
            <input type="text" value="" id="notifyUserInput" name="notifyUserInput"/>

            <?php
            $userSearchUrl = Url::toRoute(['/user/search/json', 'keyword' => '-keywordPlaceholder-']);
            if ($contentContainer instanceof Space) {
                $userSearchUrl = $contentContainer->createUrl('/space/membership/search', array('keyword' => '-keywordPlaceholder-'));
            }

            /* add UserPickerWidget to notify members */
            echo \humhub\modules\user\widgets\UserPicker::widget(array(
                'inputId' => 'notifyUserInput',
                'userSearchUrl' => $userSearchUrl,
                'maxUsers' => 10,
                'userGuid' => Yii::$app->user->guid,
                'placeholderText' => Yii::t('ContentModule.widgets_views_contentForm', 'Add a member to notify'),
                'focus' => true,
            ));
            ?>
        </div>

        <?php
        echo Html::hiddenInput("containerGuid", $contentContainer->guid);
        echo Html::hiddenInput("containerClass", get_class($contentContainer));
        ?>

        <div class="contentForm_options">

            <hr>

            <div class="btn_container">

                <?php echo \humhub\widgets\LoaderWidget::widget(['id' => 'postform-loader', 'cssClass' => 'loader-postform hidden']); ?>
                
                <?php
                echo \humhub\widgets\AjaxButton::widget([
                    'label' => $submitButtonText,
                    'ajaxOptions' => [
                        'url' => $submitUrl,
                        'type' => 'POST',
                        'dataType' => 'json',
                        'beforeSend' => "function() { $('.contentForm').removeClass('error'); $('#contentFormError').hide(); $('#contentFormError').empty(); }",
                        'beforeSend' => 'function(){ $("#contentFormError").hide(); $("#contentFormError li").remove(); $(".contentForm_options .btn").hide(); $("#postform-loader").removeClass("hidden"); }',
                        'success' => "function(response) { handleResponse(response);}"
                    ],
                    'htmlOptions' => [
                        'id' => "post_submit_button",
                        'data-action' => 'post_create',
                        'class' => 'btn btn-info',
                        'type' => 'submit'
                ]]);
                ?>
                <?php
                // Creates Uploading Button
                echo humhub\modules\file\widgets\FileUploadButton::widget(array(
                    'uploaderId' => 'contentFormFiles',
                    'fileListFieldName' => 'fileList',
                ));
                ?>
                <script>
                    $('#fileUploaderButton_contentFormFiles').bind('fileuploaddone', function (e, data) {
                        $('.btn_container').show();
                    });
                    $('#fileUploaderButton_contentFormFiles').bind('fileuploadprogressall', function (e, data) {
                        var progress = parseInt(data.loaded / data.total * 100, 10);
                        if (progress != 100) {
                            // Fix: remove focus from upload button to hide tooltip
                            $('#post_submit_button').focus();
                            // hide form buttons
                            $('.btn_container').hide();
                        }
                    });</script>


                <!-- public checkbox -->
                <?php echo Html::checkbox("visibility", "", array('id' => 'contentForm_visibility', 'class' => 'contentForm hidden')); ?>

                <!-- content sharing -->
                <div class="pull-right">

                    <span class="label label-success label-public hidden"><?php echo Yii::t('ContentModule.widgets_views_contentForm', 'Public'); ?></span>

                    <ul class="nav nav-pills preferences" style="right: 0; top: 5px;">
                        <li class="dropdown">
                            <a class="dropdown-toggle" style="padding: 5px 10px;" data-toggle="dropdown" href="#"><i
                                    class="fa fa-cogs"></i></a>
                            <ul class="dropdown-menu pull-right">
                                <li>
                                    <a href="javascript:notifyUser();"><i
                                            class="fa fa-bell"></i> <?php echo Yii::t('ContentModule.widgets_views_contentForm', 'Notify members'); ?>
                                    </a>
                                </li>
                                <?php if ($canSwitchVisibility): ?>
                                    <li>
                                        <a id="contentForm_visibility_entry" href="javascript:changeVisibility();"><i
                                                class="fa fa-unlock"></i> <?php echo Yii::t('ContentModule.widgets_views_contentForm', 'Make public'); ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                    </ul>


                </div>

            </div>

            <?php
            // Creates a list of already uploaded Files
            echo \humhub\modules\file\widgets\FileUploadList::widget(array(
                'uploaderId' => 'contentFormFiles'
            ));
            ?>

        </div>
        <!-- /contentForm_Options -->
        <?php echo Html::endForm(); ?>
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
    
    setDefaultVisibility();
        
    function setDefaultVisibility() {
        <?php if ($defaultVisibility == humhub\modules\content\models\Content::VISIBILITY_PRIVATE) : ?>
            setPrivateVisibility();
        <?php endif ;?>
                
        <?php if ($defaultVisibility == humhub\modules\content\models\Content::VISIBILITY_PUBLIC) : ?>
            setPublicVisibility();
        <?php endif ;?>
    }
    
    function setPublicVisibility() {
        $('#contentForm_visibility').prop( "checked", true );
        $('#contentForm_visibility_entry').html('<i class="fa fa-lock"></i> <?php echo Yii::t('ContentModule.widgets_views_contentForm', 'Make private'); ?>');
        $('.label-public').removeClass('hidden');
    }
    
    function setPrivateVisibility() {
        $('#contentForm_visibility').prop( "checked", false );
        $('#contentForm_visibility_entry').html('<i class="fa fa-unlock"></i> <?php echo Yii::t('ContentModule.widgets_views_contentForm', 'Make public'); ?>');
        $('.label-public').addClass('hidden');
    }

    function changeVisibility() {
        if (!$('#contentForm_visibility').prop('checked')) {
            setPublicVisibility();
        } else {
            setPrivateVisibility();
        }
    }

    function notifyUser() {
        $('#notifyUserContainer').removeClass('hidden');
        $('#notifyUserInput_tag_input_field').focus();
    }

    function handleResponse(response) {
        if (!response.errors) {
            // application.modules_core.wall function
            currentStream.prependEntry(response.wallEntryId);

            // Reset Form (Empty State)
            jQuery('.contentForm_options').hide();
            $('.contentForm').filter(':text').val('');
            $('.contentForm').filter('textarea').val('').trigger('autosize.resize');
            $('.contentForm').attr('checked', false);
            $('.userInput').remove(); // used by UserPickerWidget
            $('#notifyUserContainer').addClass('hidden');
            $('#notifyUserInput').val('');
            
            setDefaultVisibility();
            
            $('#contentFrom_files').val('');
            $('#public').attr('checked', false);
            $('#contentForm_message_contenteditable').html('<?php echo Html::encode(Yii::t("ContentModule.widgets_views_contentForm", "What's on your mind?")); ?>');
            $('#contentForm_message_contenteditable').addClass('atwho-placeholder');
            
            $('#contentFormBody').find('.atwho-input').trigger('clear');
            
            // Notify FileUploadButtonWidget to clear (by providing uploaderId)
            resetUploader('contentFormFiles');
        } else {
            $('#contentFormError').show();
            $.each(response.errors, function (fieldName, errorMessage) {
                // Mark Fields as Error
                fieldId = 'contentForm_' + fieldName;
                $('#' + fieldId).addClass('error');
                $.each(errorMessage, function (key, msg) {
                    $('#contentFormError').append('<li><i class=\"icon-warning-sign\"></i> ' + msg + '</li>');
                });
            });
        }
        $('.contentForm_options .btn').show();
        $('#postform-loader').addClass('hidden');
    }
</script>