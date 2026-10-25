<?php

use humhub\components\View;
use humhub\modules\user\widgets\AccountMenu;
use humhub\widgets\ContentCard;
use humhub\widgets\FooterMenu;

/**
 * @var View $this
 * @var string $content
 *
 * A page without a content card of its own (older module pages) gets a panel around it.
 */
?>
<div class="container">
    <div class="row">
        <div class="col-lg-3">
            <?php
            echo AccountMenu::widget(); ?>
        </div>
        <div class="col-lg-9">
            <?php if (ContentCard::isRendered($this)) : ?>
                <?= $content ?>
            <?php else : ?>
                <div class="panel panel-default">
                    <?= $content ?>
                </div>
            <?php endif; ?>
            <?= FooterMenu::widget(['location' => FooterMenu::LOCATION_FULL_PAGE]); ?>
        </div>
    </div>
</div>
