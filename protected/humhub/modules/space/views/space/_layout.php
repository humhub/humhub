<?php
/**
 * @var \humhub\modules\space\models\Space $space
 * @var string $content
 */

use humhub\modules\space\widgets\Header;
use humhub\modules\space\widgets\Menu;
use humhub\modules\space\widgets\SpaceContent;
use humhub\modules\space\widgets\Sidebar;
use humhub\modules\activity\widgets\Stream;
use humhub\modules\space\modules\manage\widgets\PendingApprovals;
use humhub\modules\space\widgets\Members;
use humhub\widgets\FooterMenu;

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
                ]);
                ?>
                <?= FooterMenu::widget(['location' => FooterMenu::LOCATION_FULL_PAGE]); ?>
            </div>
        <?php else : ?>
            <div class="col-md-7 layout-content-container">
                <?= SpaceContent::widget([
                    'contentContainer' => $space,
                    'content' => $content
                ]);
                ?>
            </div>
            <div class="col-md-3 layout-sidebar-container">
                <?= Sidebar::widget(['space' => $space, 'widgets' => [
                        [PendingApprovals::className(), ['space' => $space], ['sortOrder' => 10]],
                        [Stream::className(), ['streamAction' => '/space/space/stream', 'contentContainer' => $space], ['sortOrder' => 20]],
                        [Members::className(), ['space' => $space], ['sortOrder' => 30]]
                ]]);
                ?>
                <?= FooterMenu::widget(['location' => FooterMenu::LOCATION_SIDEBAR]); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
