<?php

use humhub\compat\CActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

?>

<div class="content_edit input-container" id="comment_edit_<?php echo $comment->id; ?>">
    <?php $form = CActiveForm::begin(); ?>
    <?php echo Html::hiddenInput('contentModel', $contentModel); ?>
    <?php echo Html::hiddenInput('contentId', $contentId); ?>
    <?php echo $form->textArea($comment, 'message', array('class' => 'form-control', 'id' => 'comment_input_' . $comment->id, 'placeholder' => Yii::t('CommentModule.views_edit', 'Edit your comment...'))); ?>

    <!-- create contenteditable div for HEditorWidget to place the data -->
    <div id="comment_input_<?php echo $comment->id; ?>_contenteditable" class="form-control atwho-input"
         contenteditable="true"><?php echo \humhub\widgets\RichText::widget(['text' => $comment->message]); ?></div>


    <?php
    /* Modify textarea for mention input */
    echo \humhub\widgets\RichTextEditor::widget(array(
        'id' => 'comment_input_' . $comment->id,
        'inputContent' => $comment->message,
    ));
    ?>

    <?php
    // Creates Uploading Button
    echo humhub\modules\file\widgets\FileUploadButton::widget(array(
        'uploaderId' => 'comment_upload_' . $comment->id,
        'fileListFieldName' => 'fileList',
        'object' => $comment
    ));
    ?>


    <?php
    echo \humhub\widgets\AjaxButton::widget([
        'label' => "Save",
        'ajaxOptions' => [
            'type' => 'POST',
            'beforeSend' => new yii\web\JsExpression('function(html){  $("#comment_input_' . $comment->id . '_contenteditable").hide(); showLoader("'.$comment->id.'"); }'),
            'success' => new yii\web\JsExpression('function(html){  $("#comment_' . $comment->id . '").replaceWith(html); }'),
            'url' => Url::to(['/comment/comment/edit', 'id' => $comment->id, 'contentModel' => $comment->object_model, 'contentId' => $comment->object_id]),
        ],
        'htmlOptions' => [
            'class' => 'btn btn-primary hidden',
            'id' => 'comment_edit_post_' . $comment->id,
        ],
    ]);
    ?>

    <?php
    // Creates a list of already uploaded Files
    echo \humhub\modules\file\widgets\FileUploadList::widget(array(
        'uploaderId' => 'comment_upload_' . $comment->id,
        'object' => $comment
    ));
    ?>

    <?php CActiveForm::end(); ?>
</div>

<script type="text/javascript">

    // add attribute to manage the enter/submit event (prevent submit, if user press enter to insert an item from atwho plugin)
    $('#comment_input_<?php echo $comment->id; ?>_contenteditable').attr('data-submit', 'true');

    // Fire click event for comment button by typing enter
    $("#comment_input_<?php echo $comment->id; ?>_contenteditable").keydown(function (event) {


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

    $('#comment_input_<?php echo $comment->id; ?>_contenteditable').on("shown.atwho", function (event, flag, query) {
        // prevent the submit event, by changing the attribute
        $('#comment_input_<?php echo $comment->id; ?>_contenteditable').attr('data-submit', 'false');
    });

    $('#comment_input_<?php echo $comment->id; ?>_contenteditable').on("hidden.atwho", function (event, flag, query) {

        var interval = setInterval(changeSubmitState, 10);

        // allow the submit event, by changing the attribute (with delay, to prevent the first enter event for insert an item from atwho plugin)
        function changeSubmitState() {
            $('#comment_input_<?php echo $comment->id; ?>_contenteditable').attr('data-submit', 'true');
            clearInterval(interval);
        }
    });


    // show laoder during ajax call
    function showLoader(comment_id) {
        $('#comment_edit_' + comment_id).html('<div class="loader" style="padding: 15px 0;"><div class="sk-spinner sk-spinner-three-bounce" style="margin:0;"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div>');
    }


</script>