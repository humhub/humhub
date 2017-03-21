<div class="panel-body">
    <h4><?= Yii::t('AdminModule.setting', 'E-Mail Settings'); ?></h4>
    <div class="help-block">
        <?= Yii::t('AdminModule.setting', 'Here you can configurate the e-mail behaviour and mail-server settings of your social network.'); ?>
    </div>
</div>

<?= \humhub\modules\admin\widgets\MailSettingMenu::widget(); ?>

<div class="panel-body">
    <?= $content; ?>
</div>