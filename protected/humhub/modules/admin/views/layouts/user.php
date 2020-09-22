<?php $this->beginContent('@admin/views/layouts/main.php') ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= Yii::t('AdminModule.user', '<strong>User</strong> administration'); ?>
    </div>
    <?= \humhub\modules\admin\widgets\UserMenu::widget(); ?>

    <?= $content; ?>
</div>
<?php $this->endContent(); ?>