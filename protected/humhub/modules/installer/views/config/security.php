<?php

use humhub\modules\installer\controllers\ConfigController;
use yii\widgets\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Url;

?>
<div id="name-form" class="panel panel-default animated fadeIn">

    <div class="panel-heading">
        <?php echo Yii::t('InstallerModule.views_config_security', 'Security <strong>Settings</strong>'); ?>
    </div>

    <div class="panel-body">

        <p><?php echo Yii::t('InstallerModule.views_config_security', 'Here you can decide how new, unregistered users can access HumHub.'); ?></p>
<br>

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'internalAllowAnonymousRegistration')->checkbox(); ?>
        <?= $form->field($model, 'internalRequireApprovalAfterRegistration')->checkbox(); ?>
        <?= $form->field($model, 'allowGuestAccess')->checkbox(); ?>
        <?= $form->field($model, 'canInviteExternalUsersByEmail')->checkbox(); ?>

        <br>

        
        <hr>

        <?php echo Html::submitButton(Yii::t('base', 'Next'), array('class' => 'btn btn-primary')); ?>


<?php ActiveForm::end(); ?>
    </div>
</div>


