<?php $this->beginContent('@admin/views/layouts/main.php') ?>
<div class="card card-default">
    <div class="card-header">
        <?= Yii::t('AdminModule.user', '<strong>User</strong> administration'); ?>
    </div>
    <?= \humhub\modules\admin\widgets\UserMenu::widget(); ?>

    <?= $content; ?>
</div>
<?php $this->endContent(); ?>
