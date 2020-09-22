<div class="panel-heading">
    <?= Yii::t('UserModule.account', '<strong>Your</strong> profile'); ?> <?php echo \humhub\widgets\DataSaved::widget(); ?>
</div>

<?= humhub\modules\user\widgets\AccountProfileMenu::widget(); ?>

<div class="panel-body">
    <?php echo $content; ?>
</div>





