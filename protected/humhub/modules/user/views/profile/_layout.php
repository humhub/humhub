<?php

use humhub\modules\user\widgets\ProfileHeader;
use humhub\modules\user\widgets\ProfileMenu;
use humhub\widgets\FooterMenu;

$user = $this->context->contentContainer;

?>
<div class="container profile-layout-container">
    <div class="row">
        <div class="col-md-12">
            <?= ProfileHeader::widget(['user' => $user]); ?>
        </div>
    </div>
    <div class="row profile-content">
        <div class="col-md-2 layout-nav-container">
            <?= ProfileMenu::widget(['user' => $this->context->user]); ?>
        </div>
        <div class="col-md-10 layout-content-container">
            <?= $content; ?>
            <?= FooterMenu::widget(['location' => FooterMenu::LOCATION_FULL_PAGE]); ?>
        </div>
    </div>
</div>
