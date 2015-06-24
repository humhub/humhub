<div class="content_edit input-container" id="comment_edit_<?php echo $comment->id; ?>">
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'comment-edit-form',
        'enableAjaxValidation' => false,
    ));
    ?>
    <?php echo CHtml::hiddenField('contentModel', $contentModel); ?>
    <?php echo CHtml::hiddenField('contentId', $contentId); ?>
    <?php echo $form->textArea($comment, 'message', array('class' => 'form-control', 'id' => 'comment_input_' . $comment->id, 'placeholder' => Yii::t('CommentModule.views_edit', 'Edit your comment...'))); ?>

    <!-- create contenteditable div for HEditorWidget to place the data -->
    <div id="comment_input_<?php echo $comment->id; ?>_contenteditable" class="form-control atwho-input" contenteditable="true"><?php echo HHtml::enrichText($comment->message); ?></div>


    <?php
    /* Modify textarea for mention input */
    $this->widget('application.widgets.HEditorWidget', array(
        'id' => 'comment_input_' . $comment->id,
        'inputContent' => $comment->message,
    ));
    ?>

    <?php
    // Creates Uploading Button
    $this->widget('application.modules_core.file.widgets.FileUploadButtonWidget', array(
        'uploaderId' => 'comment_upload_' . $comment->id,
        'fileListFieldName' => 'fileList',
        'object' => $comment
    ));
    ?>    

    <?php
    echo HHtml::ajaxButton('Save', array('//comment/comment/edit', 'id' => $comment->id, 'contentModel' => $comment->object_model, 'contentId' => $comment->object_id), array(
        'type' => 'POST',
        'success' => 'function(html){  $("#comment_' . $comment->id . '").replaceWith(html); }',
            ), array('class' => 'btn btn-primary', 'id' => 'comment_edit_post_' . $comment->id, 'style' => 'position: absolute; left: -90000000px; opacity: 0;'));
    ?>

    <?php
    // Creates a list of already uploaded Files
    $this->widget('application.modules_core.file.widgets.FileUploadListWidget', array(
        'uploaderId' => 'comment_upload_' . $comment->id,
        'object' => $comment
    ));
    ?>    

    <?php $this->endWidget(); ?>
</div>

<script type="text/javascript">

    // add attribute to manage the enter/submit event (prevent submit, if user press enter to insert an item from atwho plugin)
    $('#comment_input_<?php echo $comment->id; ?>_contenteditable').attr('data-submit', 'true');

    // Fire click event for comment button by typing enter
    $("#comment_input_<?php echo $comment->id; ?>_contenteditable").keydown(function(event) {


        // by pressing enter without shift
        if (event.keyCode == 13 && event.shiftKey == false) {

            // prevent default behavior
            event.cancelBubble = true;
            event.returnValue = false;
            event.preventDefault();


            // check if a submit is allowed
            if ($('#comment_input_<?php echo $comment->id; ?>_contenteditable').attr('data-submit') == 'true') {

                // hide all tooltips (specially for file upload button)
                $('.tt').tooltip('hide');

                // check if a submit is allowed
                if ($('#comment_input_<?php echo $comment->id; ?>_contenteditable').attr('data-submit') == 'true') {

                    // get plain input text from contenteditable DIV
                    $('#comment_input_<?php echo $comment->id; ?>').val(getPlainInput($('#comment_input_<?php echo $comment->id; ?>_contenteditable').clone()));

                    // set focus to submit button
                    $('#comment_edit_post_<?php echo $comment->id; ?>').focus();

                    // emulate the click event
                    $('#comment_edit_post_<?php echo $comment->id; ?>').click();

                }
            }
        }

        return event.returnValue;

    });

    $('#comment_input_<?php echo $comment->id; ?>_contenteditable').on("shown.atwho", function(event, flag, query) {
        // prevent the submit event, by changing the attribute
        $('#comment_input_<?php echo $comment->id; ?>_contenteditable').attr('data-submit', 'false');
    });

    $('#comment_input_<?php echo $comment->id; ?>_contenteditable').on("hidden.atwho", function(event, flag, query) {

        var interval = setInterval(changeSubmitState, 10);

        // allow the submit event, by changing the attribute (with delay, to prevent the first enter event for insert an item from atwho plugin)
        function changeSubmitState() {
            $('#comment_input_<?php echo $comment->id; ?>_contenteditable').attr('data-submit', 'true');
            clearInterval(interval);
        }
    });

</script>