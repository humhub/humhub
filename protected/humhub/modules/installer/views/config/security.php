<?php

use yii\widgets\ActiveForm;
use yii\bootstrap\Html;
?>
<div id="name-form" class="panel panel-default animated fadeIn">

    <div class="panel-heading">
        <?php echo Yii::t('InstallerModule.views_config_security', 'Security <strong>Settings</strong>'); ?>
    </div>

    <div class="panel-body">

        <p><?php echo Yii::t('InstallerModule.views_config_security', 'Here you can decide how new, unregistered users can access HumHub.'); ?></p>
        <br>

        <?php $form = ActiveForm::begin(['enableClientValidation' => false]); ?>

        <?= $form->field($model, 'internalAllowAnonymousRegistration')->checkbox(); ?>
        <?= $form->field($model, 'internalRequireApprovalAfterRegistration')->checkbox(); ?>
        <?= $form->field($model, 'allowGuestAccess')->checkbox(); ?>
        <?= $form->field($model, 'canInviteExternalUsersByEmail')->checkbox(); ?>
        <?= $form->field($model, 'enableFriendshipModule')->checkbox(); ?>

        <br>
        <hr>

        <?php echo Html::submitButton(Yii::t('base', 'Next'), array('class' => 'btn btn-primary', 'data-ui-loader' => '')); ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>


