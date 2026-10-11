<?php

use humhub\components\View;
use humhub\modules\notification\widgets\SettingsPage;

/* @var $this View */
?>

<div class="panel-body">
    <h4><?= Yii::t('AdminModule.settings', 'Notification Settings') ?></h4>
    <div class="text-body-secondary">
        <?= Yii::t('NotificationModule.base', 'Notifications are sent directly to your users to inform them about new activities in your network.') ?>
        <br>
        <?= Yii::t('NotificationModule.base', 'In this view, you can define the default behavior for your users. These settings can be overwritten by users in their account settings page.') ?>
    </div>

    <?= SettingsPage::widget(['scope' => SettingsPage::SCOPE_GLOBAL]) ?>
</div>
