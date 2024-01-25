<?php $this->beginContent('@admin/views/layouts/main.php') ?>
<div class="card card-default">
    <div class="card-header">
        <?= Yii::t('AdminModule.user', '<strong>Information</strong>'); ?>
    </div>
    <?= \humhub\modules\admin\widgets\InformationMenu::widget(); ?>

    <div class="card-body">
        <?= $content; ?>
    </div>
</div>
<?php $this->endContent(); ?>
