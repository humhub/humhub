<?php
humhub\modules\admin\widgets\SettingsMenu::markAsActive(['/admin/setting/advanced']);
?>
<div class="panel-body">
    <h4><?= Yii::t('AdminModule.setting', 'Advanced Settings'); ?></h4>
    <div class="help-block">
        <?= Yii::t('AdminModule.setting', 'These settings refer to advanced topics of your social network.'); ?>
    </div>
</div>

<?= humhub\modules\admin\widgets\AdvancedSettingMenu::widget(); ?>

<div class="panel-body">
    <?= $content; ?>
</div>