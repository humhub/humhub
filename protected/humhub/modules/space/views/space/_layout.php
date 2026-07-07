<?php

use humhub\components\View;
use humhub\helpers\Html;
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
        <aside class="col-lg-2 layout-nav-container" aria-label="<?= Html::encode(Yii::t('base', 'Sidebar')) ?>">
            <?= Menu::widget(['space' => $space]); ?>
        </aside>
        <div class="col-lg-<?= ($this->hasSidebar()) ? '7' : '10' ?> layout-content-container">
            <?= SpaceContent::widget(['contentContainer' => $space, 'content' => $content]) ?>
        </div>
        <?php if ($this->hasSidebar()): ?>
            <aside class="col-lg-3 layout-sidebar-container" aria-label="<?= Html::encode(Yii::t('base', 'Sidebar')) ?>">
                <?= $this->getSidebar() ?>
                <?= FooterMenu::widget(['location' => FooterMenu::LOCATION_SIDEBAR]); ?>
            </aside>
        <?php endif; ?>
    </div>

    <?php if (!$this->hasSidebar()): ?>
        <?= FooterMenu::widget(['location' => FooterMenu::LOCATION_FULL_PAGE]); ?>
    <?php endif; ?>
</div>
