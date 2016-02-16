<?php

use yii\helpers\Html;
use yii\helpers\Url;
use humhub\widgets\AjaxButton;

?>

<?php /* BEGIN: Comment Create Form */ ?>
<div id="comment_create_form_<?php echo $id; ?>" class="comment_create">

    <?php echo Html::beginForm("#"); ?>
    <?php echo Html::hiddenInput('contentModel', $modelName); ?>
    <?php echo Html::hiddenInput('contentId', $modelId); ?>

    <?php echo Html::textArea("message", "", array('id' => 'newCommentForm_' . $id, 'rows' => '1', 'class' => 'form-control autosize commentForm', 'placeholder' => Yii::t('CommentModule.widgets_views_form', 'Write a new comment...'))); ?>

    <?php echo humhub\widgets\RichTextEditor::widget(['id' => 'newCommentForm_' . $id]); ?>

    <div class="comment-buttons">

    <?php
    // Creates Uploading Button
    echo humhub\modules\file\widgets\FileUploadButton::widget(array(
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
            $('#loader-" . $id . "').remove();
            $('#newCommentForm_" . $id . "_contenteditable').show();
            $('.comment-buttons').show();
            resetUploader('comment_upload_" . $id . "');
            $('#newCommentForm_" . $id . "_contenteditable').focus();
    }";

    echo AjaxButton::widget([
        'label' => Yii::t('CommentModule.widgets_views_form', 'Send'),
        'ajaxOptions' => [
            'type' => 'POST',
            'beforeSend' => new yii\web\JsExpression("function(html){  $('#newCommentForm_" . $id . "_contenteditable').hide(); $('.comment-buttons').hide(); showLoader('" . $id . "'); }"),
            'success' => new yii\web\JsExpression($jsSuccess),
            'url' => Url::to(['/comment/comment/post']),
        ],
        'htmlOptions' => [
            'id' => "comment_create_post_" . $id,
            'class' => 'btn btn-sm btn-default btn-comment-submit pull-left',
            'type' => 'submit'
        ],
    ]);
    ?>

    </div>

    <?php echo Html::endForm(); ?>


    <?php
    // Creates a list of already uploaded Files
    echo \humhub\modules\file\widgets\FileUploadList::widget(array(
        'uploaderId' => 'comment_upload_' . $id,
    ));
    ?>
</div>

<script>

    $(document).ready(function () {

        // set the size for one row (Firefox)
        $('#newCommentForm_<?php echo $id; ?>').css({height: '36px'});

        // add autosize function to input
        $('.autosize').autosize();

    });

    // show loader during ajax call
    function showLoader(comment_id) {
        $('#newCommentForm_' + comment_id + '_contenteditable').after('<div class="loader" id="loader-' + comment_id + '" style="padding: 15px 0;"><div class="sk-spinner sk-spinner-three-bounce" style="margin:0;"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div>');
    }

</script>
