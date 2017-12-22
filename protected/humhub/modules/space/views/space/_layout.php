<?php

use humhub\modules\space\widgets\Header;
use humhub\modules\space\widgets\Menu;
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
        <div class="col-md-10 layout-content-container">
            <?= SpaceContent::widget(['contentContainer' => $space, 'content' => $content]) ?>
        </div>
    </div>
</div>
