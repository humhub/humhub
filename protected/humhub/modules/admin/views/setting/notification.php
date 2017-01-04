<?php
use yii\widgets\ActiveForm;
/* @var $model \humhub\modules\notification\models\forms\NotificationSettings */
?>

<div class="panel-body">
    <h4><?= Yii::t('AdminModule.setting', 'Notification Settings'); ?></h4>
    <div class="help-block">
        <?= Yii::t('AdminModule.setting', 
                'Here you can configure the default notification behaviour for your users.'); ?><br />
        <?= Yii::t('AdminModule.setting', 'You can enable outgoing notifications for a notification category by choosing the disired notification targets.'); ?>
    </div>

    <br />
    <?php $form = ActiveForm::begin() ?>
     <?= humhub\modules\notification\widgets\NotificationSettingsForm::widget([
         'model' => $model,
         'form' => $form
     ]) ?>
    <?php ActiveForm::end(); ?> 
</div>

