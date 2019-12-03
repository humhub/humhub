<?php
    use yii\bootstrap\ActiveForm;
?>

<?php $this->beginContent('@user/views/account/_userProfileLayout.php') ?>
    <div class="help-block">
        <?= Yii::t('UserModule.views_account_edit', 'The info you add here will be visible on the About page of your profile.<br/>You can control who can see your About page under Security Settings.'); ?>
    </div>
    <?php $form = ActiveForm::begin(['enableClientValidation' => false, 'options' => ['data-ui-widget' => 'ui.form.TabbedForm', 'data-ui-init' => '', 'style' => 'display:none']]); ?>
        <?= $hForm->render($form); ?>
    <?php ActiveForm::end(); ?>
<?php $this->endContent(); ?>

