<?php

use humhub\libs\Html;

/* @var $spaceId int */
/* @var $newMembershipButton string */
?>

<div class="modal-dialog animated fadeIn">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel">
                <?= Yii::t('SpaceModule.base', "<strong>Request</strong> space membership"); ?>
            </h4>
        </div>
        <div class="modal-body">

            <div class="text-center">
                <?= Yii::t('SpaceModule.base', 'Your request was successfully submitted to the space administrators.'); ?>
            </div>

        </div>
        <div class="modal-footer">
            <hr>
            <button type="button" class="btn btn-primary" data-dismiss="modal">
                <?= Yii::t('SpaceModule.base', 'Close'); ?>
            </button>
        </div>
    </div>
</div>
<script <?= Html::nonce() ?>>
    $('[data-space-request-membership=<?= $spaceId ?>]').replaceWith('<?= $newMembershipButton ?>');
</script>
