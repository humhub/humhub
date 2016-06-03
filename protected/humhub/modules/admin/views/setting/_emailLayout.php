<div class="panel-body">
    <h4><?php echo Yii::t('AdminModule.setting', 'E-Mail Settings'); ?></h4>
    <div class="help-block">
        <?php echo Yii::t('AdminModule.setting', 'Here you can configurate the e-mail behaviour and mail-server settings of your social network.'); ?>
    </div>
</div>

<?php echo humhub\modules\admin\widgets\MailSettingMenu::widget(); ?>


<div class="panel-body">
    <?php echo $content; ?>
</div>