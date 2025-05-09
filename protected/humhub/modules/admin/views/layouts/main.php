<?php

use humhub\modules\admin\widgets\AdminMenu;
use humhub\widgets\FooterMenu;

/** @var $content string */
?>
<div class="container">
    <div class="row">
        <div class="col-lg-3">
            <?= AdminMenu::widget(); ?>
        </div>
        <div class="col-lg-9">
            <?= $content; ?>
            <?= FooterMenu::widget(); ?>
        </div>
    </div>
</div>
