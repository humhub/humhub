<?php 
 humhub\assets\TabbedFormAsset::register($this);
?>

<div class="panel-heading">
    <?= Yii::t('UserModule.account', '<strong>User</strong> settings'); ?> <?php echo \humhub\widgets\DataSaved::widget(); ?>
</div>

<?php // humhub\modules\user\widgets\AccountSettingsMenu::widget(); ?>

<div class="panel-body">
    <?= $content; ?>
</div>





