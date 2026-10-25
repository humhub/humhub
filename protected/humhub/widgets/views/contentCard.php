<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\helpers\Html;

/* @var $this View */
/* @var $id string */
/* @var $title string */
/* @var $titleTag string */
/* @var $description string */
/* @var $buttons string */
/* @var $actions string */
/* @var $menu string */
/* @var $tabs string */
/* @var $content string */
/* @var $footer string */
/* @var $options array */

$titleId = $id . '-title';
Html::addCssClass($options, ['card', 'content-card']);
if ($title !== '') {
    $options['aria-labelledby'] = $titleId;
}
?>
<?= Html::beginTag($title !== '' ? 'section' : 'div', $options) ?>
    <?php if ($title !== '' || $buttons !== '' || $actions !== '' || $menu !== '') : ?>
        <div class="card-header">
            <?php if ($title !== '') : ?>
                <div class="card-heading">
                    <?= Html::tag($titleTag, Html::encode($title), ['id' => $titleId, 'class' => 'card-title']) ?>
                    <?php if ($description !== '') : ?>
                        <p class="card-subtitle"><?= $description ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($buttons !== '' || $actions !== '' || $menu !== '') : ?>
                <div class="card-header-actions">
                    <?= $buttons ?>
                    <?= $actions ?>
                    <?= $menu ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php if ($tabs !== '') : ?>
        <div class="card-tabs"><?= $tabs ?></div>
    <?php endif; ?>
    <div class="card-body">
        <?= $content ?>
    </div>
    <?php if ($footer !== '') : ?>
        <div class="card-footer"><?= $footer ?></div>
    <?php endif; ?>
<?= Html::endTag($title !== '' ? 'section' : 'div') ?>
