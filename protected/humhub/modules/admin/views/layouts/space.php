<?php
\humhub\modules\admin\widgets\AdminMenu::markAsActive('spaces');
?>

<?php $this->beginContent('@admin/views/layouts/main.php') ?>
<div class="card card-default">
    <div class="card-header"><?= Yii::t('AdminModule.space', '<strong>Manage</strong> Spaces'); ?></div>
    <?= \humhub\modules\admin\widgets\SpaceMenu::widget(); ?>
    <div class="card-body">
        <?= $content ?>
    </div>
</div>

<?php $this->endContent(); ?>
