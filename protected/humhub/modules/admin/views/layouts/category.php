<?php
use yii\helpers\Html;

\humhub\modules\admin\widgets\AdminMenu::markAsActive(['/admin/category']);
?>

<?php $this->beginContent('@admin/views/layouts/main.php') ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= Yii::t('AdminModule.views_space_index', '<strong>Manage</strong> Categories'); ?>
        <?= Html::a('<i class="fa fa-plus" aria-hidden="true"></i>&nbsp;&nbsp;' . Yii::t('AdminModule.category', 'Add new category'), ['/admin/category/create'], ['class' => 'btn btn-sm btn-success pull-right', 'data-target' => '#globalModal']); ?>
    </div>
    <?= \humhub\modules\admin\widgets\CategoryMenu::widget(); ?>
    <div class="panel-body">
        <?= $content ?>
    </div>
</div>

<?php $this->endContent(); ?>