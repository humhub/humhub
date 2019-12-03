<?php
 use humhub\libs\Html;
?>

<?php \humhub\widgets\ModalDialog::begin(['header' => Yii::t('CommentModule.base', 'Comments')]) ?>
    <div class="modal-body comment-container comment-modal-body" style="margin-top:0px">
        <div id="userlist-content">
            <div class="well well-small" id="comment_<?= $id; ?>">
                <div class="comment" id="comments_area_<?= $id; ?>">
                    <?= $output; ?>
                </div>
                <?= humhub\modules\comment\widgets\Form::widget(['object' => $object]); ?>
            </div>
        </div>
    </div>
<?php \humhub\widgets\ModalDialog::end() ?>
<script <?= Html::nonce() ?>>

    // scroll to top of list
    $(".comment-modal-body").animate({scrollTop: 0}, 200);

</script>


