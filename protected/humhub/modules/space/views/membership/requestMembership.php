<?php

use humhub\helpers\Html;
use humhub\modules\space\assets\SpaceAsset;
use humhub\modules\space\models\forms\RequestMembershipForm;
use humhub\modules\space\models\Space;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;
use yii\web\View;

/**
 * @var $this View
 * @var $space Space
 * @var $model RequestMembershipForm
 */

SpaceAsset::register($this);

?>

<?php $form = Modal::beginFormDialog([
    'title' => Yii::t('SpaceModule.base', '<strong>Request</strong> Membership'),
    'footer' =>
        ModalButton::cancel() . ' ' .
        ModalButton::primary(Yii::t('SpaceModule.base', 'Send'))
            ->action('space.requestMembershipSend', $space->createUrl('/space/membership/request-membership-form')),
]) ?>

        <?= Yii::t('SpaceModule.base', 'Access to this Space is restricted. Please introduce yourself to become a member.'); ?>

        <br/>
        <br/>

        <?= $form->field($model, 'message')->textarea([
            'id' => 'request-message',
            'placeholder' => Yii::t('SpaceModule.base', 'I want to become a member because...'),
        ]) ?>

<?php Modal::endFormDialog(); ?>


<script <?= Html::nonce() ?>>
    // set focus to input field
    $('#request-message').focus()

    // Shake modal after wrong validation
    <?php if ($model->hasErrors()): ?>
    $('.modal-dialog').removeClass('fadeIn');
    $('.modal-dialog').addClass('shake');
    <?php endif; ?>
</script>
