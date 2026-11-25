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
/* @var $title string the title as HTML */
/* @var $titleTag string */
/* @var $titleAction array|null */
/* @var $menu string */
/* @var $collapseId string|null */
/* @var $content string */
/* @var $options array */
/* @var $bodyOptions array */

$titleId = $id . '-title';
Html::addCssClass($options, ['card', 'content-card', 'sidebar-card']);
if ($title !== '') {
    $options['aria-labelledby'] = $titleId;
}

Html::addCssClass($bodyOptions, 'card-body');
if ($collapseId !== null) {
    // `ui.sidebarCard.SidebarCard` restores the remembered state; visible until then
    $options['data-ui-widget'] = 'ui.sidebarCard.SidebarCard';
    $options['data-ui-init'] = true;
    $bodyOptions['id'] = $collapseId;
    Html::addCssClass($bodyOptions, ['collapse', 'show']);
}

if ($titleAction !== null) {
    Html::addCssClass($titleAction, 'card-title-action');
    $title = Html::button($title, $titleAction);
}
?>
<?= Html::beginTag($title !== '' ? 'section' : 'div', $options) ?>
    <?php if ($title !== '' || $menu !== '') : ?>
        <div class="card-header">
            <?php if ($title !== '') : ?>
                <?= Html::tag($titleTag, $title, ['id' => $titleId, 'class' => 'card-title']) ?>
            <?php endif; ?>
            <?php if ($menu !== '') : ?>
                <div class="card-header-actions"><?= $menu ?></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?= Html::tag('div', $content, $bodyOptions) ?>
<?= Html::endTag($title !== '' ? 'section' : 'div') ?>
