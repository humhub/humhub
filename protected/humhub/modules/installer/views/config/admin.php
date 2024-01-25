<?php
use humhub\libs\Html;

/**
 * @var $hForm \humhub\compat\HForm
 * @see \humhub\modules\installer\controllers\ConfigController::actionAdmin()
 */
?>
<div id="create-admin-account-form" class="card card-default animated fadeIn">

    <div class="card-header">
        <?php echo Yii::t('InstallerModule.base', '<strong>Admin</strong> Account'); ?>
    </div>

    <div class="card-body">
        <p><?php echo Yii::t('InstallerModule.base', "You're almost done. In this step you have to fill out the form to create an admin account. With this account you can manage the whole network."); ?></p>
        <hr/>

        <?php $form = \yii\widgets\ActiveForm::begin(['enableClientValidation' => false]); ?>
        <?= $hForm->render($form); ?>
        <?php \yii\widgets\ActiveForm::end(); ?>
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

