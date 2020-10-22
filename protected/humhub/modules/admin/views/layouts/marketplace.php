<?php

use humhub\modules\admin\widgets\AdminMenu;
use humhub\modules\admin\widgets\MarketplaceMenu;

AdminMenu::markAsActive(['/admin/marketplace']);
?>

<?php $this->beginContent('@admin/views/layouts/main.php') ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= Yii::t('AdminModule.views_space_index', '<strong>Manage</strong> Marketplaces'); ?>
    </div>
    <?= MarketplaceMenu::widget(); ?>
    <div class="panel-body">
        <?= $content ?>
    </div>
</div>

<?php $this->endContent(); ?>
