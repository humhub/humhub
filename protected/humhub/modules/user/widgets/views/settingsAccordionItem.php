<?php

use humhub\helpers\Html;

/* @var string $id */
/* @var string $accordionId */
/* @var bool $expanded */
/* @var string $title */
/* @var string $description */
/* @var string $actions */
/* @var string $content */
?>
<div class="accordion-item">
    <h3 class="accordion-header" id="<?= $id ?>-header">
        <button class="accordion-button<?= $expanded ? '' : ' collapsed' ?>" type="button" data-bs-toggle="collapse"
                data-bs-target="#<?= $id ?>-body" aria-expanded="<?= $expanded ? 'true' : 'false' ?>" aria-controls="<?= $id ?>-body">
            <span class="accordion-title">
                <?= Html::encode($title) ?>
                <?php if ($description !== ''): ?>
                    <span class="accordion-subtitle"><?= Html::encode($description) ?></span>
                <?php endif ?>
            </span>
        </button>
        <?php if ($actions !== ''): ?>
            <span class="accordion-header-actions"><?= $actions ?></span>
        <?php endif ?>
    </h3>
    <div id="<?= $id ?>-body" class="accordion-collapse collapse<?= $expanded ? ' show' : '' ?>"
         data-bs-parent="#<?= $accordionId ?>" role="region" aria-labelledby="<?= $id ?>-header">
        <div class="accordion-body"><?= $content ?></div>
    </div>
</div>
