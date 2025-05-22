<div class="panel-heading">
    <?= Yii::t('UserModule.account', '<strong>Your</strong> profile'); ?>
</div>

<?= humhub\modules\user\widgets\AccountProfileMenu::widget(); ?>

<div class="panel-body">
    <?= $content ?>
</div>
