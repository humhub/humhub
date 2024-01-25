<?php
\humhub\modules\admin\widgets\AdminMenu::markAsActive('settings');
?>

<?php $this->beginContent('@admin/views/layouts/main.php') ?>
<div class="card card-default">
    <div class="card-header">
        <?= Yii::t('AdminModule.user', '<strong>Settings</strong> and Configuration'); ?>
    </div>
    <?= \humhub\modules\admin\widgets\SettingsMenu::widget(); ?>

    <?= $content; ?>
</div>
<?php $this->endContent(); ?>
