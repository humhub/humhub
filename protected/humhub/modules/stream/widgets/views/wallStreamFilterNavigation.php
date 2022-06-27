<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

use humhub\modules\stream\widgets\WallStreamFilterNavigation;
use humhub\modules\ui\filter\widgets\FilterPanel;
use humhub\widgets\Button;
use yii\helpers\Html;

/* @var $this \humhub\modules\ui\view\components\View */
/* @var $panels [] */
/* @var $options [] */

$panelColumn1Blocks = isset($panels[WallStreamFilterNavigation::PANEL_COLUMN_1]) ? $panels[WallStreamFilterNavigation::PANEL_COLUMN_1] : null;
$panelColumn2Blocks = isset($panels[WallStreamFilterNavigation::PANEL_COLUMN_2]) ? $panels[WallStreamFilterNavigation::PANEL_COLUMN_2] : null;
$panelColumn3Blocks = isset($panels[WallStreamFilterNavigation::PANEL_COLUMN_3]) ? $panels[WallStreamFilterNavigation::PANEL_COLUMN_3] : null;
$panelColumn4Blocks = isset($panels[WallStreamFilterNavigation::PANEL_COLUMN_4]) ? $panels[WallStreamFilterNavigation::PANEL_COLUMN_4] : null;

?>

<?= Html::beginTag('div', $options) ?>

    <div class="wall-stream-filter-root nav-tabs">
        <div class="wall-stream-filter-head clearfix">
            <div class="wall-stream-filter-bar"></div>
            <?= Button::asLink(Yii::t('ContentModule.base', 'Filter') . '<b class="caret"></b>')
                ->cssClass('wall-stream-filter-toggle')->icon('fa-filter')->sm()->style('pa') ?>
        </div>
        <div class="wall-stream-filter-body" style="display:none">
            <div class="filter-root">
                <div class="row">
                    <?= FilterPanel::widget(['blocks' => $panelColumn1Blocks, 'span' => count($panels)])?>
                    <?= FilterPanel::widget(['blocks' => $panelColumn2Blocks, 'span' => count($panels)])?>
                    <?= FilterPanel::widget(['blocks' => $panelColumn3Blocks, 'span' => count($panels)])?>
                </div>
            </div>
        </div>
    </div>

<?= Html::endTag('div') ?>
