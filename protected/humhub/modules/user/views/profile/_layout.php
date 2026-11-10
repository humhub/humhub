<?php

use humhub\helpers\Html;
use humhub\modules\user\widgets\ProfileHeader;
use humhub\modules\user\widgets\ProfileMenu;
use humhub\widgets\FooterMenu;

$user = $this->context->contentContainer;

?>
<div class="container profile-layout-container">
    <div class="row">
        <div class="col-lg-12">
            <?= ProfileHeader::widget(['user' => $user]); ?>
        </div>
    </div>
    <div class="row profile-content">
        <?php /* No landmark of its own: the menu in it is the `<nav>` (one "Sidebar" per page, the column on the right) */ ?>
        <div class="col-lg-2 layout-nav-container">
            <?= ProfileMenu::widget(['user' => $user]); ?>
        </div>
        <div id="layout-content-column" class="col-lg-<?= ($this->hasSidebar()) ? '7' : '10' ?> layout-content-container">
            <?= $content; ?>
            <?php if (!$this->hasSidebar()): ?>
                <?= FooterMenu::widget(['location' => FooterMenu::LOCATION_FULL_PAGE]); ?>
            <?php endif; ?>
        </div>
        <?php if ($this->hasSidebar()): ?>
            <aside id="layout-sidebar" class="col-lg-3 layout-sidebar-container" aria-label="<?= Html::encode(Yii::t('base', 'Sidebar')) ?>">
                <?= $this->getSidebar() ?>
                <?= FooterMenu::widget(['location' => FooterMenu::LOCATION_SIDEBAR]); ?>
            </aside>
        <?php endif; ?>
    </div>
</div>
