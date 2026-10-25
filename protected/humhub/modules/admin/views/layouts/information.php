<?php

use humhub\modules\admin\widgets\AdminMenu;
use humhub\modules\admin\widgets\InformationMenu;
use humhub\widgets\PageHeader;

/* @var $content string */

// Also on the pages modules add to this section
AdminMenu::markAsActive('information');
?>
<?php $this->beginContent('@admin/views/layouts/main.php') ?>
<div class="d-flex flex-column gap-3">
    <?= PageHeader::title(Yii::t('AdminModule.base', 'Information'))->tabs(InformationMenu::class) ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <?= $content ?>
        </div>
    </div>
</div>
<?php $this->endContent() ?>
