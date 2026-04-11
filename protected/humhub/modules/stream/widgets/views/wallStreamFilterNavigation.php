<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\stream\widgets\WallStreamFilterNavigation;
use humhub\modules\ui\filter\widgets\FilterPanel;
use humhub\widgets\bootstrap\Link;

/* @var $this View */
/* @var $panels [] */
/* @var $options [] */
/* @var $isCollapsed bool */

$panelColumn1Blocks = $panels[WallStreamFilterNavigation::PANEL_COLUMN_1] ?? null;
$panelColumn2Blocks = $panels[WallStreamFilterNavigation::PANEL_COLUMN_2] ?? null;
$panelColumn3Blocks = $panels[WallStreamFilterNavigation::PANEL_COLUMN_3] ?? null;
$panelColumn4Blocks = $panels[WallStreamFilterNavigation::PANEL_COLUMN_4] ?? null;

?>

<?= Html::beginTag('div', $options) ?>

<div class="wall-stream-filter-root nav-tabs">
    <div class="wall-stream-filter-head clearfix">
        <div class="wall-stream-filter-bar"></div>
        <?= Link::to(Yii::t('ContentModule.base', 'Filter'))
            ->cssClass('wall-stream-filter-toggle filter-toggle-link')
            ->icon('filter')
            ->sm() ?>
    </div>
    <div class="wall-stream-filter-body<?= $isCollapsed ? ' d-none' : '' ?>">
        <div class="filter-root container">
            <div class="row">
                <?= FilterPanel::widget(['blocks' => $panelColumn1Blocks]) ?>
                <?= FilterPanel::widget(['blocks' => $panelColumn2Blocks]) ?>
                <?= FilterPanel::widget(['blocks' => $panelColumn3Blocks]) ?>
            </div>
        </div>
    </div>
</div>

<?= Html::endTag('div') ?>
