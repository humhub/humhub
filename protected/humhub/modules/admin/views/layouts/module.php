<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\ThemeHelper;

/* @var $content string */
?>
<div
    class="<?php if (ThemeHelper::isFluid()): ?>container-fluid<?php else: ?>container<?php endif; ?> container-cards container-modules">
    <div class="row">
        <div class="col-xl-12">
            <?= $content; ?>
        </div>
    </div>
</div>
