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
/* @var $actions string */
/* @var $menu string */
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
    <?php if ($title !== '' || $actions !== '' || $menu !== '') : ?>
        <div class="card-header">
            <?php if ($title !== '') : ?>
                <?= Html::tag($titleTag, Html::encode($title), ['id' => $titleId, 'class' => 'card-title']) ?>
            <?php endif; ?>
            <?php if ($actions !== '' || $menu !== '') : ?>
                <div class="card-header-actions">
                    <?= $actions ?>
                    <?= $menu ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <div class="card-body">
        <?= $content ?>
    </div>
    <?php if ($footer !== '') : ?>
        <div class="card-footer"><?= $footer ?></div>
    <?php endif; ?>
<?= Html::endTag($title !== '' ? 'section' : 'div') ?>
