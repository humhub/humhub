<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\Html;

/* @var string $accordionId */
/* @var string $id */
/* @var bool $open */
/* @var string $title */
/* @var string $description */
/* @var string $actions */
/* @var string $content */
?>
<div class="accordion-item">
    <h3 class="accordion-header">
        <?= Html::button(Html::tag('span', Html::encode($title), ['class' => 'accordion-title']), [
            'class' => ['accordion-button', $open ? '' : 'collapsed'],
            'data-bs-toggle' => 'collapse',
            'data-bs-target' => '#' . $id,
            'aria-expanded' => $open ? 'true' : 'false',
            'aria-controls' => $id,
        ]) ?>
    </h3>
    <?= Html::beginTag('div', [
        'id' => $id,
        'class' => ['accordion-collapse', 'collapse', $open ? 'show' : ''],
        'data-bs-parent' => '#' . $accordionId,
    ]) ?>
        <div class="accordion-body">
            <?php if ($description !== '') : ?>
                <p><?= Html::encode($description) ?></p>
            <?php endif ?>
            <?= $content ?>
            <?php if ($actions !== '') : ?>
                <div class="d-flex flex-wrap gap-2 mt-3"><?= $actions ?></div>
            <?php endif ?>
        </div>
    <?= Html::endTag('div') ?>
</div>
