<?php
    humhub\modules\admin\widgets\SettingsMenu::markAsActive(['/admin/setting/advanced']); 
?>
<h4><?php echo Yii::t('AdminModule.setting', 'Advanced Settings'); ?></h4>

<br />

<?php echo humhub\modules\admin\widgets\AdvancedSettingMenu::widget(); ?>

<br />

<?php echo $content; ?>