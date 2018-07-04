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

/* @var $this \humhub\components\View */
/* @var $panels [] */
/* @var $options [] */

$leftPanelBlocks = isset($panels[WallStreamFilterNavigation::PANEL_POSITION_LEFT]) ? $panels[WallStreamFilterNavigation::PANEL_POSITION_LEFT] : null;
$centerPanelBlocks = isset($panels[WallStreamFilterNavigation::PANEL_POSITION_CENTER]) ? $panels[WallStreamFilterNavigation::PANEL_POSITION_CENTER] : null;
$rightPanelBlocks = isset($panels[WallStreamFilterNavigation::PANEL_POSITION_RIGHT]) ? $panels[WallStreamFilterNavigation::PANEL_POSITION_RIGHT] : null;

?>

<?= Html::beginTag('div', $options) ?>

    <div class="wall-stream-filter-root nav-tabs">
        <div class="wall-stream-filter-head clearfix">
            <div class="wall-stream-filter-bar"></div>
            <?= Button::asLink(Yii::t('ContentModule.widgets_views_stream', 'Filter') . '<b class="caret"></b>')
                ->cssClass('wall-stream-filter-toggle')->icon('fa-filter')->sm()->style('pa') ?>
        </div>
        <div class="wall-stream-filter-body" style="display:none">
            <div class="filter-root">
                <div class="row">
                    <?= FilterPanel::widget(['blocks' => $leftPanelBlocks, 'span' => count($panels)])?>
                    <?= FilterPanel::widget(['blocks' => $centerPanelBlocks, 'span' => count($panels)])?>
                    <?= FilterPanel::widget(['blocks' => $rightPanelBlocks, 'span' => count($panels)])?>
                </div>
            </div>
        </div>
    </div>

<?= Html::endTag('div') ?>