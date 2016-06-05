<?php

use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
?>
<div class="modal-dialog modal-dialog-small animated fadeIn">
    <div class="modal-content">
        <?php $form = ActiveForm::begin(); ?>
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"
                id="myModalLabel"><?php echo Yii::t('UserModule.invite', '<strong>Invite</strong> new people'); ?></h4>
        </div>
        <div class="modal-body">

            <br/>

            <?php echo Yii::t('UserModule.invite', 'Please add the email addresses of people you want to invite below.'); ?>
            <br/><br/>
            <div class="form-group">
                <?php echo $form->field($model, 'emails')->textArea(['rows' => '3', 'placeholder' => Yii::t('UserModule.invite', 'Email address(es)'), 'id' => 'emails'])->label(false)->hint(Yii::t('UserModule.invite', 'Separate multiple email addresses by comma.')); ?>
            </div>
        </div>
        <div class="modal-footer">

            <?php
            echo \humhub\widgets\AjaxButton::widget([
                'label' => Yii::t('UserModule.invite', 'Send invite'),
                'ajaxOptions' => [
                    'type' => 'POST',
                    'beforeSend' => new yii\web\JsExpression('function(){ setModalLoader(); }'),
                    'success' => new yii\web\JsExpression('function(html){ $("#globalModal").html(html); }'),
                    'url' => Url::to(['/user/invite']),
                ],
                'htmlOptions' => [
                    'class' => 'btn btn-primary'
                ]
            ]);
            ?>

            <?php echo \humhub\widgets\LoaderWidget::widget(['id' => 'invite-loader', 'cssClass' => 'loader-modal hidden']); ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

</div>