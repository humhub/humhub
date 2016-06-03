<?php 
 humhub\assets\TabbedFormAsset::register($this);
?>

<div class="panel-heading">
    <?php echo Yii::t('UserModule.account', '<strong>User</strong> settings'); ?> <?php echo \humhub\widgets\DataSaved::widget(); ?>
</div>

<?= humhub\modules\user\widgets\AccountSettingsMenu::widget(); ?>

<div class="panel-body">
    <?php echo $content; ?>
</div>





