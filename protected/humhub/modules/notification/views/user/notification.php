<?php

use humhub\components\View;
use humhub\modules\notification\widgets\SettingsPage;
use humhub\modules\notification\widgets\UserInfoWidget;

/* @var $this View */
?>

<div class="panel-heading">
    <?= Yii::t('NotificationModule.base', '<strong>Notification</strong> Settings') ?>
</div>
<div class="panel-body">
    <div class="text-body-secondary">
        <?= Yii::t('NotificationModule.base', 'Notifications are sent instantly to you to inform you about new activities in your network.') ?>
    </div>

    <?= UserInfoWidget::widget() ?>

    <?= SettingsPage::widget() ?>
</div>
