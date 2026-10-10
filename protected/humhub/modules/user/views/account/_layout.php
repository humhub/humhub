<?php

use humhub\components\View;
use humhub\modules\user\widgets\AccountMenu;
use humhub\widgets\FooterMenu;

/**
 * @var View $this
 * @var string $content
 *
 * A page that brings its own cards (e.g. the notification settings) sets
 * `$this->params['accountPanel'] = false` to be rendered without the surrounding panel.
 */
?>
<div class="container">
    <div class="row">
        <div class="col-lg-3">
            <?php
            echo AccountMenu::widget(); ?>
        </div>
        <div class="col-lg-9">
            <?php if ($this->params['accountPanel'] ?? true): ?>
                <div class="panel panel-default">
                    <?php echo $content; ?>
                </div>
            <?php else: ?>
                <?= $content ?>
            <?php endif; ?>
            <?= FooterMenu::widget(['location' => FooterMenu::LOCATION_FULL_PAGE]); ?>
        </div>
    </div>
</div>
