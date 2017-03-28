<?php $this->beginContent('@admin/views/layouts/main.php') ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= Yii::t('AdminModule.user', '<strong>Information</strong>'); ?>
    </div>
    <?= \humhub\modules\admin\widgets\InformationMenu::widget(); ?>

    <div class="panel-body">
        <?= $content; ?>
    </div>
</div>
<?php $this->endContent(); ?>