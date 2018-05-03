<?php

use humhub\modules\activity\widgets\ActivityStreamViewer;
use humhub\modules\space\modules\manage\widgets\PendingApprovals;
use humhub\modules\space\widgets\Members;
use humhub\modules\space\widgets\Menu;
use humhub\modules\space\widgets\Sidebar;
use humhub\modules\space\widgets\Header;
use humhub\modules\space\widgets\SpaceContent;

/**
 * @var \humhub\modules\space\models\Space $space
 * @var string $content
 */

$space = $this->context->contentContainer;
?>
<div class="container space-layout-container">
    <div class="row">
        <div class="col-md-12">
            <?= Header::widget(['space' => $space]); ?>

        </div>
    </div>
    <div class="row space-content">
        <div class="col-md-2 layout-nav-container">
            <?= Menu::widget(['space' => $space]); ?>
            <br>
        </div>

        <?php if (isset($this->context->hideSidebar) && $this->context->hideSidebar) : ?>
            <div class="col-md-10 layout-content-container">
                <?= SpaceContent::widget([
                    'contentContainer' => $space,
                    'content' => $content
                ]) ?>
            </div>
        <?php else: ?>
            <div class="col-md-7 layout-content-container">
                <?= SpaceContent::widget([
                    'contentContainer' => $space,
                    'content' => $content
                ]) ?>
            </div>
            <div class="col-md-3 layout-sidebar-container">
                <?= Sidebar::widget(['space' => $space, 'widgets' => [
                        [ActivityStreamViewer::className(), ['contentContainer' => $space], ['sortOrder' => 10]],
                        [PendingApprovals::className(), ['space' => $space], ['sortOrder' => 20]],
                        [Members::className(), ['space' => $space], ['sortOrder' => 30]]
                ]]);?>
            </div>
        <?php endif; ?>
    </div>
</div>
