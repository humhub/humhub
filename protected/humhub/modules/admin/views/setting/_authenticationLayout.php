<div class="panel-body">
    <h4><?php echo Yii::t('AdminModule.setting', 'User Settings'); ?></h4>
    <div class="help-block">
        <?php echo Yii::t('AdminModule.setting', 'Here you can configurate the registration behaviour and additinal user settings of your social network.'); ?>
    </div>
</div>

<?php echo humhub\modules\admin\widgets\AuthenticationMenu::widget(); ?>
<?php echo $content; ?>