<?php 
humhub\assets\TabbedFormAsset::register($this);
?>

<div class="panel-heading">
    <?= Yii::t('UserModule.account', '<strong>Your</strong> profile'); ?> <?= \humhub\widgets\DataSaved::widget(); ?>
</div>

<?= humhub\modules\user\widgets\AccountProfilMenu::widget(); ?>

<div class="panel-body">
    <?= $content; ?>
</div>





