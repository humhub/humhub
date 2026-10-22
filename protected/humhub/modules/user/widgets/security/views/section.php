<?php

use humhub\helpers\Html;

/* @var string $title */
/* @var string $description */
/* @var string $content */
?>
<section class="security-section mb-4">
    <h2 class="h5 mb-1"><?= Html::encode($title) ?></h2>
    <?php if ($description !== ''): ?>
        <p class="text-body-secondary"><?= Html::encode($description) ?></p>
    <?php endif ?>
    <?= $content ?>
</section>
