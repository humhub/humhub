<?php

use humhub\components\View;
use humhub\modules\notification\widgets\SettingsPage;
use humhub\widgets\ContentCard;

/* @var $this View */
?>

<?php ContentCard::begin([
    'title' => Yii::t('AdminModule.base', 'Notifications'),
    'description' => Yii::t('NotificationModule.base', 'In this view, you can define the default behavior for your users. These settings can be overwritten by users in their account settings page.'),
]) ?>
<?= SettingsPage::widget(['scope' => SettingsPage::SCOPE_GLOBAL]) ?>
<?php ContentCard::end() ?>
