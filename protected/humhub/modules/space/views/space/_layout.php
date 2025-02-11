<?php

use humhub\components\View;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Header;
use humhub\modules\space\widgets\Menu;
use humhub\modules\space\widgets\SpaceContent;
use humhub\widgets\FooterMenu;

/**
 * @var View $this
 * @var Space $space
 * @var string $content
 */

/** @var ContentContainerController $context */
$context = $this->context;
$space = $context->contentContainer;

?>
<div class="container space-layout-container">
    <div class="row">
        <div class="col-lg-12">
            <?= Header::widget(['space' => $space]); ?>
        </div>
    </div>
    <div class="row space-content">
        <div class="col-lg-2 layout-nav-container">
            <?= Menu::widget(['space' => $space]); ?>
        </div>
        <div class="col-lg-<?= ($this->hasSidebar()) ? '7' : '10' ?> layout-content-container">
            <?= SpaceContent::widget(['contentContainer' => $space, 'content' => $content]) ?>
        </div>
        <?php if ($this->hasSidebar()): ?>
            <div class="col-lg-3 layout-sidebar-container">
                <?= $this->getSidebar() ?>
                <?= FooterMenu::widget(['location' => FooterMenu::LOCATION_SIDEBAR]); ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!$this->hasSidebar()): ?>
        <?= FooterMenu::widget(['location' => FooterMenu::LOCATION_FULL_PAGE]); ?>
    <?php endif; ?>
</div>
