<?php

use humhub\modules\admin\widgets\AdminMenu;
use humhub\modules\admin\widgets\TagMenu;
use yii\helpers\Html;

AdminMenu::markAsActive(['/admin/tag']);
?>

<?php $this->beginContent('@admin/views/layouts/main.php') ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= Yii::t('AdminModule.views_tag_index', '<strong>Manage</strong> Tags'); ?>
        <?= Html::a('<i class="fa fa-plus" aria-hidden="true"></i>&nbsp;&nbsp;' . Yii::t('AdminModule.tag', 'Add new tag'), ['/admin/tag/create'], ['class' => 'btn btn-sm btn-success pull-right', 'data-target' => '#globalModal']); ?>
    </div>
    <?= TagMenu::widget(); ?>
    <div class="panel-body">
        <?= $content ?>
    </div>
</div>

<?php $this->endContent(); ?>
