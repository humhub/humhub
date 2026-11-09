<?php

use humhub\components\View;
use humhub\modules\user\widgets\AccountMenu;
use humhub\modules\user\widgets\AccountSettingsMenu;
use humhub\widgets\ContentCard;
use humhub\widgets\PageHeader;

/* @var View $this */
/* @var string $content */

// Also marks the entry active on module pages that render inside this layout
AccountMenu::markAsActive('account-settings-settings');
$tabs = new AccountSettingsMenu();
?>
<div class="d-flex flex-column gap-3">
    <?= PageHeader::title(Yii::t('UserModule.account', 'Account'))->tabs($tabs) ?>
    <?php ContentCard::begin([
        'title' => $tabs->getActiveLabel() ?? Yii::t('UserModule.account', 'Account'),
    ]) ?>
        <?= $content ?>
    <?php ContentCard::end() ?>
</div>
