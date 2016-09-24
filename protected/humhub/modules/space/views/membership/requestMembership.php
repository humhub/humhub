<?php

use humhub\compat\CActiveForm;
?>
<div class="modal-dialog animated fadeIn">
    <div class="modal-content">
        <?php $form = CActiveForm::begin(); ?>
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"
                id="myModalLabel"><?php echo Yii::t('SpaceModule.views_space_requestMembership', "<strong>Request</strong> space membership"); ?></h4>
        </div>
        <div class="modal-body">

            <?php echo Yii::t('SpaceModule.views_space_requestMembership', 'Please shortly introduce yourself, to become an approved member of this space.'); ?>

            <br/>
            <br/>

            <?php //echo $form->labelEx($model, 'message');  ?>
            <?php echo $form->textArea($model, 'message', array('rows' => '8', 'class' => 'form-control', 'id' => 'request-message')); ?>
            <?php echo $form->error($model, 'message'); ?>

        </div>
        <div class="modal-footer">
            <hr/>
            <?php
            echo \humhub\widgets\AjaxButton::widget([
                'label' => Yii::t('UserModule.views_profile_cropProfileImage', 'Save'),
                'ajaxOptions' => [
                    'type' => 'POST',
                    'beforeSend' => new yii\web\JsExpression('function(){ setModalLoader(); }'),
                    'success' => new yii\web\JsExpression('function(html){ $("#globalModal").html(html); }'),
                    'url' => $space->createUrl('/space/membership/request-membership-form'),
                ],
                'htmlOptions' => [
                    'class' => 'btn btn-primary'
                ]
            ]);
            ?>
            <button type="button" class="btn btn-primary"
                    data-dismiss="modal"><?php echo Yii::t('SpaceModule.views_space_requestMembership', 'Close'); ?></button>

            <?php echo \humhub\widgets\LoaderWidget::widget(['id' => 'send-loader', 'cssClass' => 'loader-modal hidden']); ?>

        </div>

        <?php CActiveForm::end(); ?>
    </div>

</div>


<script type="text/javascript">

    // set focus to input field
    $('#request-message').focus()

    // Shake modal after wrong validation
<?php if ($model->hasErrors()) { ?>
        $('.modal-dialog').removeClass('fadeIn');
        $('.modal-dialog').addClass('shake');
<?php } ?>

</script>
