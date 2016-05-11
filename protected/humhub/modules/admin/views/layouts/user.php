<?php $this->beginContent('@admin/views/layouts/main.php') ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?php echo Yii::t('AdminModule.user', '<strong>User</strong> administration'); ?>
    </div>
    <?= \humhub\modules\admin\widgets\UserMenu::widget(); ?>

    <div class="panel-body">
        <?php echo $content; ?>
    </div>
</div>
<?php $this->endContent(); ?>