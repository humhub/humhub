<?php

humhub\modules\admin\widgets\SettingsMenu::markAsActive(['/admin/setting/advanced']);
?>
<div class="card-body">
    <h4><?= Yii::t('AdminModule.settings', 'Advanced Settings'); ?></h4>
    <div class="form-text">
        <?= Yii::t('AdminModule.settings', 'These settings refer to advanced topics of your social network.'); ?>
    </div>
</div>

<?= humhub\modules\admin\widgets\AdvancedSettingMenu::widget(); ?>

<div class="card-body">
    <?= $content; ?>
</div>
