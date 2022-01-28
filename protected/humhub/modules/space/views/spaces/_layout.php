<?php

use humhub\modules\ui\view\helpers\ThemeHelper;

/* @var $content string */
?>
<div class="<?php if (ThemeHelper::isFluid()): ?>container-fluid<?php else: ?>container<?php endif; ?> container-directory container-spaces">
    <?= $content; ?>
</div>
