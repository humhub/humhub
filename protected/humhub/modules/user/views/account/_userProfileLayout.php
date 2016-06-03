<?php 
 humhub\assets\TabbedFormAsset::register($this);
?>

<div class="panel-heading">
    <?php echo Yii::t('UserModule.account', '<strong>Your</strong> profile'); ?> <?php echo \humhub\widgets\DataSaved::widget(); ?>
</div>

<?= humhub\modules\user\widgets\AccountProfilMenu::widget(); ?>

<div class="panel-body">
    <?php echo $content; ?>
</div>





