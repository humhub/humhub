<?php

use humhub\compat\HForm;
use yii\bootstrap\ActiveForm;

/* @var $hForm HForm */
?>

<?php $this->beginContent('@user/views/account/_userProfileLayout.php') ?>
    <div class="help-block">
        <?= Yii::t('UserModule.account', 'Here you can edit your general profile data, which is visible in the about page of your profile.'); ?>
    </div>
    <?php $form = ActiveForm::begin(['enableClientValidation' => false, 'options' => ['data-ui-widget' => 'ui.form.TabbedForm', 'data-ui-init' => '', 'style' => 'display:none']]); ?>
        <?= $hForm->render($form) ?>
    <?php ActiveForm::end(); ?>
<?php $this->endContent(); ?>

