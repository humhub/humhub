<?php

use humhub\helpers\Html;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

/* @var $spaceId int */
/* @var $newMembershipButton string */
?>

<?php Modal::beginDialog([
    'title' => Yii::t('SpaceModule.base', '<strong>Request</strong> Membership'),
    'footer' => ModalButton::cancel(Yii::t('base', 'Close')),
]) ?>
    <div class="text-center">
        <?= Yii::t('SpaceModule.base', 'Your request was successfully submitted to the space administrators.'); ?>
    </div>
<?php Modal::endDialog() ?>

<script <?= Html::nonce() ?>>
    $('[data-space-request-membership=<?= $spaceId ?>]').replaceWith('<?= $newMembershipButton ?>');
</script>
