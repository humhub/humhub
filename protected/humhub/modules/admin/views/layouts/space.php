<?php

use humhub\components\View;
use humhub\modules\admin\widgets\AdminMenu;
use humhub\modules\admin\widgets\SpaceMenu;
use humhub\widgets\ContentCard;
use humhub\widgets\PageHeader;

/* @var $this View */
/* @var $content string */

AdminMenu::markAsActive('spaces');
?>
<?php $this->beginContent('@admin/views/layouts/main.php') ?>
<div class="d-flex flex-column gap-3">
    <?= PageHeader::title(Yii::t('AdminModule.base', 'Spaces'))->tabs(SpaceMenu::class) ?>
    <?php if (ContentCard::isRendered($this)) : ?>
        <?= $content ?>
    <?php else : ?>
        <div class="panel panel-default">
            <div class="panel-body">
                <?= $content ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $this->endContent() ?>
