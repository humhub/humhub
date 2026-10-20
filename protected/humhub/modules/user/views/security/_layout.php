<?php

use humhub\modules\user\widgets\AccountMenu;
use humhub\modules\user\widgets\SecuritySettingsMenu;

/* @var string $content */

// Also marks the entry active on module pages that render inside this layout
AccountMenu::markAsActive('account-settings-security');
?>
<div class="panel-heading">
    <?= Yii::t('UserModule.account', '<strong>Security</strong> &amp; Privacy') ?>
</div>
<div class="panel-body">
    <?= Yii::t('UserModule.account', 'Manage how you sign in, where you are logged in and what happens with your data.') ?>
</div>

<?= SecuritySettingsMenu::widget() ?>

<div class="panel-body">
    <?= $content ?>
</div>
