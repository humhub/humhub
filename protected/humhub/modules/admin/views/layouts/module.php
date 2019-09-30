<?php $this->beginContent('@admin/views/layouts/main.php') ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= Yii::t('AdminModule.base', '<strong>Module </strong> administration'); ?>

        <div class="help-block">
            <?= Yii::t('AdminModule.modules', 'Modules extend the functionality of HumHub. Here you can install and manage modules from the HumHub Marketplace.') ?>
        </div>

    </div>
    <?= \humhub\modules\admin\widgets\ModuleMenu::widget(); ?>

    <?= $content; ?>
</div>
<?php $this->endContent(); ?>
