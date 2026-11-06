<?php

use humhub\components\View;
use humhub\modules\user\widgets\AccountMenu;
use humhub\modules\user\widgets\AppearanceSettingsMenu;
use humhub\widgets\ContentCard;
use humhub\widgets\PageHeader;

/* @var View $this */
/* @var string $content */

AccountMenu::markAsActive('account-settings-appearance');
$tabs = new AppearanceSettingsMenu();
?>
<div class="d-flex flex-column gap-3">
    <?= PageHeader::title(Yii::t('UserModule.account', 'Appearance & Accessibility'))->tabs($tabs) ?>
    <?php ContentCard::begin([
        'title' => $tabs->getActiveLabel() ?? Yii::t('UserModule.account', 'Appearance'),
    ]) ?>
        <?= $content ?>
    <?php ContentCard::end() ?>
</div>
