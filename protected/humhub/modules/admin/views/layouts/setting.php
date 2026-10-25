<?php

use humhub\modules\admin\widgets\AdminMenu;
use humhub\modules\admin\widgets\SettingsMenu;
use humhub\widgets\PageHeader;

/* @var $content string */

AdminMenu::markAsActive('settings');
?>
<?php $this->beginContent('@admin/views/layouts/main.php') ?>
<div class="d-flex flex-column gap-3">
    <?= PageHeader::title(Yii::t('AdminModule.base', 'Settings'))->tabs(SettingsMenu::class) ?>
    <div class="panel panel-default">
        <?= $content ?>
    </div>
</div>
<?php $this->endContent() ?>
