<?php

use humhub\modules\notification\models\forms\NotificationSettings;
use humhub\modules\notification\widgets\NotificationSettingsForm;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\bootstrap\Link;
use humhub\widgets\form\ActiveForm;

/* @var $model NotificationSettings */
?>

<div class="panel-body">
    <h4><?= Yii::t('AdminModule.settings', 'Notification Settings') ?></h4>
    <div class="text-body-secondary">
        <?= Yii::t('NotificationModule.base', 'Notifications are sent directly to your users to inform them about new activities in your network.'); ?>
        <br/>
        <?= Yii::t('NotificationModule.base', 'In this view, you can define the default behavior for your users. These settings can be overwritten by users in their account settings page.'); ?>
        <br/>
    </div>
    <?php $form = ActiveForm::begin(['acknowledge' => true]) ?>
    <?= NotificationSettingsForm::widget([
        'model' => $model,
        'form' => $form,
        'showSpaces' => true,
    ]) ?>
    <br>

    <?= Button::save()->submit() ?>

    <?php if ($model->canResetAllUsers()): ?>
        <?= Link::danger(Yii::t('NotificationModule.base', 'Reset for all users'))
            ->post(['reset-all-users'])
            ->confirm(null, Yii::t('NotificationModule.base', 'Do you want to reset the settings concerning notifications for all users?'))
            ->right() ?>
    <?php endif ?>

    <?php ActiveForm::end() ?>
</div>
