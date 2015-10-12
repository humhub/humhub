<?php

use humhub\compat\CActiveForm;
use humhub\compat\CHtml;
use yii\helpers\Url;

?>
<div class="content_edit" id="post_edit_<?php echo $post->id; ?>">
    <?php $form = CActiveForm::begin(['id' => 'post-edit-form']); ?>

    <?php echo $form->textArea($post, 'message', array('class' => 'form-control', 'id' => 'post_input_' . $post->id, 'placeholder' => Yii::t('PostModule.views_edit', 'Edit your post...'))); ?>

    <!-- create contenteditable div for HEditorWidget to place the data -->
    <div id="post_input_<?php echo $post->id; ?>_contenteditable" class="form-control atwho-input"
         contenteditable="true"><?php echo \humhub\widgets\RichText::widget(['text' => $post->message]); ?></div>

    <?= \humhub\widgets\RichTextEditor::widget(['id' => 'post_input_' . $post->id, 'inputContent' => $post->message]); ?>

    <?php
    // Creates Uploading Button
    echo humhub\modules\file\widgets\FileUploadButton::widget(array(
        'uploaderId' => 'post_upload_' . $post->id,
        'object' => $post
    ));
    ?>


    <?php
    echo \humhub\widgets\AjaxButton::widget([
        'label' => 'Save',
        'ajaxOptions' => [
            'type' => 'POST',
            'beforeSend' => new yii\web\JsExpression('function(html){  $("#post_input_' . $post->id . '_contenteditable").hide(); showLoader("' . $post->id . '"); }'),
            'success' => new yii\web\JsExpression('function(html){ $(".wall_' . $post->getUniqueId() . '").replaceWith(html); }'),
            'url' => $post->content->container->createUrl('/post/post/edit', ['id' => $post->id]),
        ],
        'htmlOptions' => [
            'class' => 'btn btn-primary',
            'id' => 'post_edit_post_' . $post->id,
            'style' => 'position: absolute; left: -90000000px; opacity: 0;'
        ]
    ]);
    ?>

    <?php
    // Creates a list of already uploaded Files
    echo \humhub\modules\file\widgets\FileUploadList::widget(array(
        'uploaderId' => 'post_upload_' . $post->id,
        'object' => $post
    ));
    ?>

    <?php CActiveForm::end(); ?>
</div>

<script type="text/javascript">

    $('#post_input_<?php echo $post->id; ?>_contenteditable').focus();

    // Hide file area of post
    $('#post-files-<?php echo $post->id; ?>').hide();
    $('#files-<?php echo $post->id; ?>').hide();

    // add attribute to manage the enter/submit event (prevent submit, if user press enter to insert an item from atwho plugin)
    $('#post_input_<?php echo $post->id; ?>_contenteditable').attr('data-submit', 'true');

    // Fire click event for post button by typing enter
    $("#post_input_<?php echo $post->id; ?>_contenteditable").keydown(function (event) {


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

                // get plain input text from contenteditable DIV
                $('#post_input_<?php echo $post->id; ?>').val(getPlainInput($('#post_input_<?php echo $post->id; ?>_contenteditable').clone()));

                // emulate the click event
                $('#post_edit_post_<?php echo $post->id; ?>').focus();
                $('#post_edit_post_<?php echo $post->id; ?>').click();
            }
        }

        return event.returnValue;

    });

    $('#post_input_<?php echo $post->id; ?>_contenteditable').on("shown.atwho", function (event, flag, query) {
        // prevent the submit event, by changing the attribute
        $('#post_input_<?php echo $post->id; ?>_contenteditable').attr('data-submit', 'false');
    });

    $('#post_input_<?php echo $post->id; ?>_contenteditable').on("hidden.atwho", function (event, flag, query) {

        var interval = setInterval(changeSubmitState, 10);

        // allow the submit event, by changing the attribute (with delay, to prevent the first enter event for insert an item from atwho plugin)
        function changeSubmitState() {
            $('#post_input_<?php echo $post->id; ?>_contenteditable').attr('data-submit', 'true');
            clearInterval(interval);
        }
    });


    // show laoder during ajax call
    function showLoader(post_id) {
        $('#post_edit_' + post_id).html('<div class="loader" style="padding: 15px 0;"><div class="sk-spinner sk-spinner-three-bounce" style="margin:0;"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div>');
    }


</script>