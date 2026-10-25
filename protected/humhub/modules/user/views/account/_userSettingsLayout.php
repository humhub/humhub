<?php

use humhub\components\View;
use humhub\modules\user\widgets\AccountMenu;
use humhub\modules\user\widgets\AccountSettingsMenu;
use humhub\widgets\PageHeader;

/* @var View $this */
/* @var string $content */

AccountMenu::markAsActive(['/user/account/edit-settings']);
$this->params['accountPanel'] = false;
?>
<div class="d-flex flex-column gap-3">
    <?= PageHeader::title(Yii::t('UserModule.account', 'General'))->tabs(AccountSettingsMenu::class) ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <p class="text-body-secondary">
                <?= Yii::t('UserModule.account', 'Define basic settings for your profile. You can add tags that fit you, choose the language and your time zone and block impolite users.') ?>
            </p>
            <?= $content ?>
        </div>
    </div>
</div>
