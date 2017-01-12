<?php

use yii\widgets\ActiveForm;

?>

<?php $this->beginContent('@user/views/account/_userSettingsLayout.php') ?>
    <div class="help-block">
        <?= Yii::t('UserModule.views_account_notification', 'This view allows you to configure your notification settings by selecting the desired targets for the given notification categories.'); ?>
    </div>
    <?php $form = ActiveForm::begin(); ?>

        <?= humhub\modules\notification\widgets\NotificationSettingsForm::widget([
                'model' => $model,
                'form' => $form
        ]) ?>
        <br />
        <button type="submit" class="btn btn-primary" data-ui-loader><?= Yii::t('base', 'Save');?></button>

    <?php ActiveForm::end(); ?>
<?php $this->endContent() ?>


