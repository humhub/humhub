<?php
\humhub\modules\admin\widgets\AdminMenu::markAsActive(['/admin/space']);
?>

<?php $this->beginContent('@admin/views/layouts/main.php') ?>
<div class="panel panel-default">
    <div class="panel-heading"><?= Yii::t('AdminModule.space', '<strong>Manage</strong> spaces'); ?></div>
    <?= \humhub\modules\admin\widgets\SpaceMenu::widget(); ?>
    <div class="panel-body">
        <?= $content ?>
    </div>
</div>

<?php $this->endContent(); ?>