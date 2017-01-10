<?php
use yii\widgets\ActiveForm;
/* @var $model \humhub\modules\notification\models\forms\NotificationSettings */
?>

<div class="panel-body">
    <h4><?= Yii::t('AdminModule.setting', 'Notification Settings'); ?></h4>
    <div class="help-block">
        <?= Yii::t('AdminModule.setting', 
                'Here you can configure the default notification behaviour for your users.'); ?>
    </div>
    <br />
    <?php $form = ActiveForm::begin() ?>
        <?= humhub\modules\notification\widgets\NotificationSettingsForm::widget([
            'model' => $model,
            'form' => $form
        ]) ?>
        <br />
        <button type="submit" class="btn btn-primary" data-ui-loader><?= Yii::t('base', 'Save');?></button>
    <?php ActiveForm::end(); ?> 
</div>

