<?php

use humhub\modules\installer\forms\SecurityForm;
use humhub\modules\ui\form\widgets\ActiveForm;
use yii\bootstrap5\Html;

/* @var SecurityForm $model */
?>
<div id="name-form" class="card card-default animated fadeIn">
    <div class="card-header">
        <?php echo Yii::t('InstallerModule.base', 'Security <strong>Settings</strong>'); ?>
    </div>

    <div class="card-body">
        <p><?php echo Yii::t('InstallerModule.base', 'Here you can decide how new, unregistered users can access HumHub.'); ?></p>
        <br>

        <?php $form = ActiveForm::begin(['enableClientValidation' => false]); ?>

        <?= $form->field($model, 'internalAllowAnonymousRegistration')->checkbox(); ?>
        <?= $form->field($model, 'internalRequireApprovalAfterRegistration')->checkbox(); ?>
        <?= $form->field($model, 'allowGuestAccess')->checkbox(); ?>
        <?= $form->field($model, 'canInviteExternalUsersByEmail')->checkbox(); ?>
        <?= $form->field($model, 'canInviteExternalUsersByLink')->checkbox(); ?>
        <?= $form->field($model, 'enableFriendshipModule')->checkbox(); ?>

        <hr>

        <?php echo Html::submitButton(Yii::t('base', 'Next'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']); ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>


