<?php

use humhub\libs\Html;
use humhub\modules\space\assets\SpaceAsset;
use humhub\modules\space\models\forms\RequestMembershipForm;
use humhub\modules\space\models\Space;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\widgets\LoaderWidget;
use yii\web\View;

/**
 * @var $this View
 * @var $space Space
 * @var $model RequestMembershipForm
 */

SpaceAsset::register($this);

?>
<div class="modal-dialog animated fadeIn">
    <div class="modal-content">
        <?php $form = ActiveForm::begin(); ?>
        <?= $form->field($model, 'options')->hiddenInput()->label(false); ?>
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel">
                <?= Yii::t('SpaceModule.base', '<strong>Request</strong> Membership'); ?>
            </h4>
        </div>
        <div class="modal-body">

            <?= Yii::t('SpaceModule.base', 'Access to this Space is restricted. Please introduce yourself to become a member.'); ?>

            <br/>
            <br/>

            <?= $form->field($model, 'message',)->textarea(['id' => 'request-message', 'placeholder' => Yii::t('SpaceModule.base', 'I want to become a member because...')]); ?>

        </div>
        <div class="modal-footer">
            <hr/>
            <?= Html::button(
                Yii::t('SpaceModule.base', 'Close'),
                [
                    'class' => ['btn', 'btn-default'],
                    'data' => [
                        'dismiss' => 'modal',
                    ],
                ]
            ) ?>

            <?= Html::a(
                Yii::t('SpaceModule.base', 'Send'),
                '#',
                [
                    'class' => ['btn', 'btn-primary'],
                    'data' => [
                        'action-click' => 'space.requestMembershipSend',
                        'action-url' => $space->createUrl('/space/membership/request-membership-form'),
                    ]
                ]
            ) ?>

        </div>
        <?php $form::end(); ?>
    </div>

</div>


<script <?= Html::nonce() ?>>

    // set focus to input field
    $('#request-message').focus()

    // Shake modal after wrong validation
    <?php if ($model->hasErrors()): ?>
    $('.modal-dialog').removeClass('fadeIn');
    $('.modal-dialog').addClass('shake');
    <?php endif; ?>

</script>
