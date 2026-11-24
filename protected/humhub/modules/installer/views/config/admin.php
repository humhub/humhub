<?php

use humhub\compat\HForm;
use humhub\helpers\Html;
use humhub\widgets\form\ActiveForm;

/**
 * @var $hForm HForm
 * @see \humhub\modules\installer\controllers\ConfigController::actionAdmin()
 */
?>
<div id="create-admin-account-form" class="card content-card animated fadeIn">

    <div class="card-header">
        <div class="card-title"><?php echo Yii::t('InstallerModule.base', '<strong>Admin</strong> Account'); ?></div>
    </div>

    <div class="card-body">
        <p><?php echo Yii::t('InstallerModule.base', "You're almost done. In this step you have to fill out the form to create an admin account. With this account you can manage the whole network."); ?></p>
        <hr/>

        <?php $form = ActiveForm::begin(['enableClientValidation' => false]); ?>
        <?= $hForm->render($form); ?>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<script <?= Html::nonce() ?>>

    $(function () {
        // set cursor to email field
        $('#User_username').focus();
    })

    // Shake panel after wrong validation
    <?php foreach ($hForm->models as $model) : ?>
    <?php if ($model->hasErrors()) : ?>
    $('#create-admin-account-form').removeClass('fadeIn');
    $('#create-admin-account-form').addClass('shake');
    <?php endif; ?>
    <?php endforeach; ?>

</script>
