<?php

use yii\helpers\Html;

$this->pageTitle = Yii::t('UserModule.views_auth_createAccount', 'Create Account');
?>

<div class="container" style="text-align: center;">
    <h1 id="app-title" class="animated fadeIn"><?php echo Html::encode(Yii::$app->name); ?></h1>
    <br/>
    <div class="row">
        <div id="create-account-form" class="panel panel-default animated bounceIn" style="max-width: 500px; margin: 0 auto 20px; text-align: left;">
            <div class="panel-heading"><?php echo Yii::t('UserModule.views_auth_createAccount', '<strong>Account</strong> registration'); ?></div>
            <div class="panel-body">
                <?php $form = \yii\bootstrap\ActiveForm::begin(['enableClientValidation' => false]); ?>
                <?= $hForm->render($form); ?>
                <?php \yii\bootstrap\ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function () {
        // set cursor to login field
        $('#User_username').focus();
    })

    // Shake panel after wrong validation
<?php foreach ($hForm->models as $model) : ?>
    <?php if ($model->hasErrors()) : ?>
            $('#create-account-form').removeClass('bounceIn');
            $('#create-account-form').addClass('shake');
            $('#app-title').removeClass('fadeIn');
    <?php endif; ?>
<?php endforeach; ?>

</script>
