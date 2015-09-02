<div class="content_edit" id="post_edit_<?php echo $post->id; ?>">
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'post-edit-form',
        'enableAjaxValidation' => false,
    ));
    ?>
    <?php echo $form->textArea($post, 'message', array('class' => 'form-control', 'id' => 'post_input_' . $post->id, 'placeholder' => Yii::t('PostModule.views_edit', 'Edit your post...'))); ?>

    <!-- create contenteditable div for HEditorWidget to place the data -->
    <div id="post_input_<?php echo $post->id; ?>_contenteditable" class="form-control atwho-input" contenteditable="true"><?php echo HHtml::enrichText($post->message); ?></div>

    <?php
    /* Modify textarea for mention input */
    $this->widget('application.widgets.HEditorWidget', array(
        'id' => 'post_input_' . $post->id,
        'inputContent' => $post->message,
    ));
    ?>

    <?php
    // Creates Uploading Button
    $this->widget('application.modules_core.file.widgets.FileUploadButtonWidget', array(
        'uploaderId' => 'post_upload_' . $post->id,
        'object' => $post
    ));
    ?>    


    <?php
    echo HHtml::ajaxButton('Save', array('//post/post/edit', 'id' => $post->id), array(
        'type' => 'POST',
        'success' => 'function(html){ $("#post-' . $post->id . '").replaceWith(html); }',
            ), array('class' => 'btn btn-primary', 'id' => 'post_edit_post_' . $post->id, 'style' => 'position: absolute; left: -90000000px; opacity: 0;'));
    ?>

    <?php
    // Creates a list of already uploaded Files
    $this->widget('application.modules_core.file.widgets.FileUploadListWidget', array(
        'uploaderId' => 'post_upload_' . $post->id,
        'object' => $post
    ));
    ?>       

    <?php $this->endWidget(); ?>
</div>

<script type="text/javascript">

    $('#post_input_<?php echo $post->id; ?>_contenteditable').focus();

    // Hide file area of post
    $('#post-files-<?php echo $post->id; ?>').hide();
    $('#files-<?php echo $post->id; ?>').hide();

    // add attribute to manage the enter/submit event (prevent submit, if user press enter to insert an item from atwho plugin)
    $('#post_input_<?php echo $post->id; ?>_contenteditable').attr('data-submit', 'true');

    // Fire click event for post button by typing enter
    $("#post_input_<?php echo $post->id; ?>_contenteditable").keydown(function(event) {


        // by pressing enter without shift
        if (event.keyCode == 13 && event.shiftKey == false) {

            // prevent default behavior
            event.cancelBubble = true;
            event.returnValue = false;
            event.preventDefault();


            // check if a submit is allowed
            if ($('#post_input_<?php echo $post->id; ?>_contenteditable').attr('data-submit') == 'true') {

                // hide all tooltips (specially for file upload button)
                $('.tt').tooltip('hide');

                // emulate the click event
                $('#post_edit_post_<?php echo $post->id; ?>').focus();
                $('#post_edit_post_<?php echo $post->id; ?>').click();
            }
        }

        return event.returnValue;

    });

    $('#post_input_<?php echo $post->id; ?>_contenteditable').on("shown.atwho", function(event, flag, query) {
        // prevent the submit event, by changing the attribute
        $('#post_input_<?php echo $post->id; ?>_contenteditable').attr('data-submit', 'false');
    });

    $('#post_input_<?php echo $post->id; ?>_contenteditable').on("hidden.atwho", function(event, flag, query) {

        var interval = setInterval(changeSubmitState, 10);

        // allow the submit event, by changing the attribute (with delay, to prevent the first enter event for insert an item from atwho plugin)
        function changeSubmitState() {
            $('#post_input_<?php echo $post->id; ?>_contenteditable').attr('data-submit', 'true');
            clearInterval(interval);
        }
    });

</script>