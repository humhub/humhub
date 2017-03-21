<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel">
                <?= Yii::t('CommentModule.views_comment_show', 'Comments'); ?>
            </h4>
        </div>

        <div class="modal-body">
            <div id="userlist-content">
                <div class="well well-small" id="comment_<?= $id; ?>">
                    <div class="comment" id="comments_area_<?= $id; ?>">
                        <?= $output; ?>
                    </div>
                    <?= \humhub\modules\comment\widgets\Form::widget(array('object' => $object)); ?>
                </div>

            </div>
        </div>
    </div>
</div>

<script>

    // scroll to top of list
    $(".modal-body").animate({scrollTop: 0}, 200);

</script>


