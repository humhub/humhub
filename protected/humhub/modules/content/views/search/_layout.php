<?php

use humhub\helpers\ThemeHelper;

/* @var $content string */
?>
<div class="<?php if (ThemeHelper::isFluid()): ?>container-fluid<?php else: ?>container<?php endif; ?> container-cards container-contents">
    <?= $content; ?>
</div>
