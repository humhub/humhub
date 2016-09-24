<?php $this->beginContent('@user/views/account/_userProfileLayout.php') ?>
    <div class="help-block">
        <?php echo Yii::t('UserModule.views_account_edit', 'Here you can edit your general profile data, which is visible in the about page of your profile.'); ?>
    </div>
    <?php $form = \yii\widgets\ActiveForm::begin(['enableClientValidation' => false, 'options' => ['data-ui-tabbed-form' => '']]); ?>
    <?php echo $hForm->render($form); ?>
    <?php \yii\widgets\ActiveForm::end(); ?>
<?php $this->endContent(); ?>

