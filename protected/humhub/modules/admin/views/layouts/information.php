<?php $this->beginContent('@admin/views/layouts/main.php') ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?php echo Yii::t('AdminModule.user', '<strong>Information</strong>'); ?>
    </div>
    <?= \humhub\modules\admin\widgets\InformationMenu::widget(); ?>

    <div class="panel-body">
        <?php echo $content; ?>
    </div>
</div>
<?php $this->endContent(); ?>