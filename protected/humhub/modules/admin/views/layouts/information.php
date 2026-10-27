<?php

use humhub\components\View;
use humhub\modules\admin\widgets\AdminMenu;
use humhub\modules\admin\widgets\InformationMenu;
use humhub\widgets\ContentCard;
use humhub\widgets\PageHeader;

/* @var $this View */
/* @var $content string */

// Also on the pages modules add to this section
AdminMenu::markAsActive('information');
$tabs = new InformationMenu();
?>
<?php $this->beginContent('@admin/views/layouts/main.php') ?>
<div class="d-flex flex-column gap-3">
    <?= PageHeader::title(Yii::t('AdminModule.base', 'Information'))->tabs($tabs) ?>
    <?php if (ContentCard::isRendered($this)) : ?>
        <?= $content ?>
    <?php else : ?>
        <?php ContentCard::begin(['title' => $tabs->getActiveLabel() ?? Yii::t('AdminModule.base', 'Information')]) ?>
            <?= $content ?>
        <?php ContentCard::end() ?>
    <?php endif; ?>
</div>
<?php $this->endContent() ?>
