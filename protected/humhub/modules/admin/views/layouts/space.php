<?php

use humhub\modules\admin\widgets\AdminMenu;
use humhub\modules\admin\widgets\SpaceMenu;

AdminMenu::markAsActive('spaces');
?>

<?php $this->beginContent('@admin/views/layouts/main.php') ?>
<div class="panel panel-default">
    <div class="panel-heading"><?= Yii::t('AdminModule.space', '<strong>Manage</strong> Spaces'); ?></div>
    <?= SpaceMenu::widget(); ?>
    <div class="panel-body">
        <?= $content ?>
    </div>
</div>

<?php $this->endContent(); ?>
