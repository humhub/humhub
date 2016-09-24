<div class="modal-dialog animated fadeIn">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"
                id="myModalLabel"><?php echo Yii::t('SpaceModule.views_space_requestMembershipSave', "<strong>Request</strong> space membership"); ?></h4>
        </div>
        <div class="modal-body">

            <div class="text-center">
                <?php echo Yii::t('SpaceModule.views_space_requestMembershipSave', 'Your request was successfully submitted to the space administrators.'); ?>
            </div>

        </div>
        <div class="modal-footer">
            <hr/>
            <button type="button" class="btn btn-primary"
                    data-dismiss="modal"><?php echo Yii::t('SpaceModule.views_space_requestMembershipSave', 'Close'); ?></button>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('#requestMembershipButton').replaceWith('<?php echo \humhub\modules\space\widgets\MembershipButton::widget(['space' => $space]) ?>');
</script>