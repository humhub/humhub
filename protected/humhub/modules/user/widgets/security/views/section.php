<?php

use humhub\helpers\Html;

/* @var string $title */
/* @var string $description */
/* @var string $actions */
/* @var string $content */
?>
<section class="security-section mb-4">
    <?php if ($actions !== ''): ?>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
            <h3 class="m-0"><?= Html::encode($title) ?></h3>
            <div class="d-flex gap-2"><?= $actions ?></div>
        </div>
    <?php else: ?>
        <h3><?= Html::encode($title) ?></h3>
    <?php endif ?>
    <?php if ($description !== ''): ?>
        <p><?= Html::encode($description) ?></p>
    <?php endif ?>
    <?= $content ?>
</section>
