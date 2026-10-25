<?php

use humhub\helpers\Html;

/* @var string $title */
/* @var string $description */
/* @var string $content */
?>
<section class="security-section mb-4">
    <h3><?= Html::encode($title) ?></h3>
    <?php if ($description !== ''): ?>
        <p><?= Html::encode($description) ?></p>
    <?php endif ?>
    <?= $content ?>
</section>
