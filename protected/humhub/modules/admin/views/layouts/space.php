<?php

use humhub\modules\admin\widgets\AdminMenu;
use humhub\modules\admin\widgets\SpaceMenu;
use humhub\widgets\PageHeader;

/* @var $content string */

AdminMenu::markAsActive('spaces');
?>
<?php $this->beginContent('@admin/views/layouts/main.php') ?>
<div class="d-flex flex-column gap-3">
    <?= PageHeader::title(Yii::t('AdminModule.base', 'Spaces'))->tabs(SpaceMenu::class) ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <?= $content ?>
        </div>
    </div>
</div>
<?php $this->endContent() ?>
