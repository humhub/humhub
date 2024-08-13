<?php
humhub\modules\user\widgets\AccountMenu::markAsActive(['/user/account/edit-settings']);
?>

<div class="panel-heading">
    <?= Yii::t('UserModule.account', '<strong>Account</strong> Settings') ?> <?php echo \humhub\widgets\DataSaved::widget(); ?>
</div>
<div class="panel-body">
    <?= Yii::t('UserModule.account', 'Define basic settings for your profile. You can add tags that fit you, choose the language and your time zone and block impolite users.') ?>
</div>

<?= humhub\modules\user\widgets\AccountSettingsMenu::widget(); ?>

<div class="panel-body">
    <?= $content; ?>
</div>





