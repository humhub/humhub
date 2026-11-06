<?php

use humhub\components\View;
use humhub\modules\admin\widgets\AdminMenu;
use humhub\modules\admin\widgets\AppearanceMenu;
use humhub\widgets\PageHeader;

/* @var $this View */
/* @var $content string */

AdminMenu::markAsActive('appearance');
?>
<?php $this->beginContent('@admin/views/layouts/main.php') ?>
<div class="d-flex flex-column gap-3">
    <?= PageHeader::title(Yii::t('AdminModule.base', 'Appearance'))->tabs(AppearanceMenu::class) ?>
    <?= $content ?>
</div>
<?php $this->endContent() ?>
