<?php
    \humhub\modules\admin\widgets\AdminMenu::markAsActive(['/admin/setting']);
?>

<?php $this->beginContent('@admin/views/layouts/main.php') ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?php echo Yii::t('AdminModule.user', '<strong>Settings</strong> and Configuration'); ?>
    </div>
    <?= \humhub\modules\admin\widgets\SettingsMenu::widget(); ?>

    <?php echo $content; ?>
</div>
<?php $this->endContent(); ?>