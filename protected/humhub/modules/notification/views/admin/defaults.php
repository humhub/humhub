<?php

use humhub\modules\ui\form\widgets\ActiveForm;
use yii\helpers\Html;

/* @var $model \humhub\modules\notification\models\forms\NotificationSettings */
?>

<div class="panel-body">
    <h4><?= Yii::t('AdminModule.settings', 'Notification Settings'); ?></h4>
    <div class="help-block">
        <?= Yii::t('NotificationModule.base', 'Notifications are sent directly to your users to inform them about new activities in your network.'); ?>
        <br/>
        <?= Yii::t('NotificationModule.base', 'In this view, you can define the default behavior for your users. These settings can be overwritten by users in their account settings page.'); ?>
        <br/>
    </div>
    <?php $form = ActiveForm::begin(['acknowledge' => true]) ?>
    <?= humhub\modules\notification\widgets\NotificationSettingsForm::widget([
        'model' => $model,
        'form' => $form,
        'showSpaces' => true
    ]) ?>
    <br/>
    <?= Html::submitButton(Yii::t('base', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>
    <?php ActiveForm::end(); ?>
</div>

