<?php

use humhub\components\View;
use humhub\modules\notification\models\forms\NotificationSettings;
use humhub\modules\notification\widgets\NotificationSettingsForm;
use humhub\modules\notification\widgets\UserInfoWidget;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\bootstrap\Link;
use humhub\widgets\form\ActiveForm;

/* @var $this View */
/* @var $model NotificationSettings */
?>

<div class="panel-heading">
    <?= Yii::t('NotificationModule.base', '<strong>Notification</strong> Settings') ?>
</div>
<div class="panel-body">
    <div class="text-body-secondary">
        <?= Yii::t('NotificationModule.base', 'Notifications are sent instantly to you to inform you about new activities in your network.') ?>
        <br>
        <?= Yii::t('NotificationModule.base', 'This view allows you to configure your notification settings by selecting the desired targets for the given notification categories.') ?>
    </div>

    <?= UserInfoWidget::widget() ?>

    <?php $form = ActiveForm::begin(['acknowledge' => true]) ?>

    <?= NotificationSettingsForm::widget([
        'model' => $model,
        'form' => $form,
    ]) ?>

    <br>
    <?= Button::save()->submit() ?>

    <?php if ($model->isTouchedSettings()): ?>
        <?= Link::light(Yii::t('ActivityModule.base', 'Reset to defaults'))
            ->post(['reset'])
            ->confirm()
            ->right() ?>
    <?php endif ?>

    <?php ActiveForm::end() ?>
</div>
