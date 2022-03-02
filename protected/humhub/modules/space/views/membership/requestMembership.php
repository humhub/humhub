<?php

use humhub\libs\Html;
use humhub\modules\space\models\forms\RequestMembershipForm;
use humhub\modules\space\models\Space;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\widgets\AjaxButton;
use humhub\widgets\LoaderWidget;

/**
 * @var $space Space
 * @var $model RequestMembershipForm
 */

?>
<div class="modal-dialog animated fadeIn">
    <div class="modal-content">
        <?php $form = ActiveForm::begin(); ?>
        <?= $form->field($model, 'options')->hiddenInput()->label(false); ?>
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel">
                <?= Yii::t('SpaceModule.base', "<strong>Request</strong> space membership"); ?>
            </h4>
        </div>
        <div class="modal-body">

            <?= Yii::t('SpaceModule.base', 'Please shortly introduce yourself, to become an approved member of this space.'); ?>

            <br/>
            <br/>

            <?= $form->field($model, 'message',)->textarea(['id' => 'request-message']); ?>

        </div>
        <div class="modal-footer">
            <hr/>
            <?= AjaxButton::widget([
                'label' => Yii::t('SpaceModule.base', 'Send'),
                'ajaxOptions' => [
                    'type' => 'POST',
                    'beforeSend' => new yii\web\JsExpression('function(){ setModalLoader(evt); }'),
                    'success' => new yii\web\JsExpression('function(html){ $("#globalModal").html(html); }'),
                    'url' => $space->createUrl('/space/membership/request-membership-form'),
                ],
                'htmlOptions' => [
                    'class' => 'btn btn-primary'
                ]
            ]); ?>

            <button type="button" class="btn btn-primary" data-dismiss="modal">
                <?= Yii::t('SpaceModule.base', 'Close'); ?>
            </button>

            <?= LoaderWidget::widget(['id' => 'send-loader', 'cssClass' => 'loader-modal hidden']); ?>
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
