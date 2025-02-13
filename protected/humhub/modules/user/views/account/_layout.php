<?php

use humhub\modules\user\widgets\AccountMenu;
use humhub\widgets\FooterMenu;

?>
<div class="container">
    <div class="row">
        <div class="col-lg-3">
            <?php
            echo AccountMenu::widget(); ?>
        </div>
        <div class="col-lg-9">
            <div class="panel panel-default">
                <?php echo $content; ?>
            </div>
            <?= FooterMenu::widget(['location' => FooterMenu::LOCATION_FULL_PAGE]); ?>
        </div>
    </div>
</div>
