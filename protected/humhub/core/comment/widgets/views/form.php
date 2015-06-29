<?php

use yii\helpers\Html;
use yii\helpers\Url;
use humhub\compat\widgets\AjaxButton;
?>

<?php /* BEGIN: Comment Create Form */ ?>
<div id="comment_create_form_<?php echo $id; ?>" class="comment_create">

    <?php echo Html::beginForm("#"); ?>
    <?php echo Html::hiddenInput('contentModel', $modelName); ?>
    <?php echo Html::hiddenInput('contentId', $modelId); ?>

    <?php echo Html::textArea("message", "", array('id' => 'newCommentForm_' . $id, 'rows' => '1', 'class' => 'form-control autosize commentForm', 'placeholder' => Yii::t('CommentModule.widgets_views_form', 'Write a new comment...'))); ?>

    <?php echo humhub\widgets\RichTextEditor::widget(['id' => 'newCommentForm_' . $id]); ?>


    <?php
    // Creates Uploading Button
    echo humhub\core\file\widgets\FileUploadButton::widget(array(
        'uploaderId' => 'comment_upload_' . $id,
        'fileListFieldName' => 'fileList',
    ));
    ?>

    <?php
    $jsSuccess = "function(html) {
            $('#comments_area_" . $id . "').append(html);
            $('#newCommentForm_" . $id . "').val('').trigger('autosize.resize');
            $('#newCommentForm_" . $id . "_contenteditable').html('" . Html::encode(Yii::t('CommentModule.widgets_views_form', 'Write a new comment...')) . "');
            $('#newCommentForm_" . $id . "_contenteditable').addClass('atwho-placeholder');
            resetUploader('comment_upload_" . $id . "');
    }";

    // TODO: Submit?
    echo AjaxButton::widget([
        'label' => Yii::t('CommentModule.widgets_views_form', 'Post'),
        'ajaxOptions' => [
            'type' => 'POST',
            'success' => new yii\web\JsExpression($jsSuccess),
            'url' => Url::to(['/comment/comment/post']),
        ],
        'htmlOptions' => [
            'id' => "comment_create_post_" . $id,
            'class' => 'btn btn-small btn-primary',
            'style' => 'position: absolute; left: -90000000px; opacity: 0;',
        ],
    ]);
    ?>    

    <?php echo Html::endForm(); ?>


    <?php
    // Creates a list of already uploaded Files
    echo \humhub\core\file\widgets\FileUploadList::widget(array(
        'uploaderId' => 'comment_upload_' . $id,
    ));
    ?>
</div>

<script>

    $(document).ready(function () {

        // add attribute to manage the enter/submit event (prevent submit, if user press enter to insert an item from atwho plugin)
        $('#newCommentForm_<?php echo $id; ?>_contenteditable').attr('data-submit', 'true');

        // Fire click event for comment button by typing enter
        $('#newCommentForm_<?php echo $id; ?>_contenteditable').keydown(function (event) {


            // by pressing enter without shift
            if (event.keyCode == 13 && event.shiftKey == false) {

                // prevent default behavior
                event.cancelBubble = true;
                event.returnValue = false;
                event.preventDefault();


                // check if a submit is allowed
                if ($('#newCommentForm_<?php echo $id; ?>_contenteditable').attr('data-submit') == 'true') {

                    // get plain input text from contenteditable DIV
                    $('#newCommentForm_<?php echo $id; ?>').val(getPlainInput($('#newCommentForm_<?php echo $id; ?>_contenteditable').clone()));

                    // set focus to submit button
                    $('#comment_create_post_<?php echo $id; ?>').focus();

                    // emulate the click event
                    $('#comment_create_post_<?php echo $id; ?>').click();

                }
            }

            return event.returnValue;

        });

        // set the size for one row (Firefox)
        $('#newCommentForm_<?php echo $id; ?>').css({height: '36px'});

        // add autosize function to input
        $('.autosize').autosize();


        $('#newCommentForm_<?php echo $id; ?>_contenteditable').on("shown.atwho", function (event, flag, query) {
            // prevent the submit event, by changing the attribute
            $('#newCommentForm_<?php echo $id; ?>_contenteditable').attr('data-submit', 'false');
        });

        $('#newCommentForm_<?php echo $id; ?>_contenteditable').on("hidden.atwho", function (event, flag, query) {

            var interval = setInterval(changeSubmitState, 10);

            // allow the submit event, by changing the attribute (with delay, to prevent the first enter event for insert an item from atwho plugin)
            function changeSubmitState() {
                $('#newCommentForm_<?php echo $id; ?>_contenteditable').attr('data-submit', 'true');
                clearInterval(interval);
            }
        });

    });

</script>
