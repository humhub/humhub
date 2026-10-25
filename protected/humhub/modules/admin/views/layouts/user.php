<?php

use humhub\modules\admin\widgets\UserMenu;
use humhub\widgets\PageHeader;

/* @var $content string */
?>
<?php $this->beginContent('@admin/views/layouts/main.php') ?>
<div class="d-flex flex-column gap-3">
    <?= PageHeader::title(Yii::t('AdminModule.base', 'Users'))->tabs(UserMenu::class) ?>
    <div class="panel panel-default">
        <?= $content ?>
    </div>
</div>
<?php $this->endContent() ?>
