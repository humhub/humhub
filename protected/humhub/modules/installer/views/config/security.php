<?php

use humhub\modules\installer\controllers\ConfigController;
use yii\widgets\ActiveForm;
use yii\bootstrap\Html;
?>
<div id="name-form" class="panel panel-default animated fadeIn">

    <div class="panel-heading">
        <?php echo Yii::t('InstallerModule.base', 'Security <strong>Settings</strong>'); ?>
    </div>

    <div class="panel-body">

        <p><?php echo Yii::t('InstallerModule.base', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.'); ?></p>


        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'internalRequireApprovalAfterRegistration')->checkbox(); ?>
        <?= $form->field($model, 'internalAllowAnonymousRegistration')->checkbox(); ?>
        <?= $form->field($model, 'allowGuestAccess')->checkbox(); ?>

        <br >
        <p><?php echo Yii::t('InstallerModule.base', '<strong>LDAP:</strong> Lorem ipsum dolor sit amet, consetetur sadipscing elitr.'); ?></p>
        
        <hr>

        <?php echo Html::submitButton(Yii::t('base', 'Next'), array('class' => 'btn btn-primary')); ?>

<?php ActiveForm::end(); ?>
    </div>
</div>


