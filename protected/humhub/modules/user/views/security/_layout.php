<?php

use humhub\components\View;
use humhub\modules\user\widgets\AccountMenu;
use humhub\modules\user\widgets\SecuritySettingsMenu;
use humhub\widgets\PageHeader;

/* @var View $this */
/* @var string $content */

// Also marks the entry active on module pages that render inside this layout
AccountMenu::markAsActive('account-settings-security');
$this->params['accountPanel'] = false;
?>
<div class="d-flex flex-column gap-3">
    <?= PageHeader::title(Yii::t('UserModule.account', 'Security & Privacy'))->tabs(SecuritySettingsMenu::class) ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <p class="text-body-secondary">
                <?= Yii::t('UserModule.account', 'Manage how you sign in, where you are logged in and what happens with your data.') ?>
            </p>
            <?= $content ?>
        </div>
    </div>
</div>
