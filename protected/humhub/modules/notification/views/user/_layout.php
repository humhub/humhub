<?php

use humhub\components\View;
use humhub\modules\notification\widgets\NotificationSettingsMenu;
use humhub\modules\user\widgets\AccountMenu;
use humhub\widgets\PageHeader;

/* @var View $this */
/* @var string $content the cards of the tab */

// Also marks the entry active on module pages that render inside this layout
AccountMenu::markAsActive('account-settings-notifications');
?>
<div class="d-flex flex-column gap-3">
    <?= PageHeader::title(Yii::t('UserModule.account', 'Notifications & E-Mails'))->tabs(new NotificationSettingsMenu()) ?>
    <?= $content ?>
</div>
