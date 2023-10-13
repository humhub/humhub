<?php

use humhub\assets\CardsAsset;
use humhub\modules\content\widgets\SearchFilters;
use humhub\modules\content\widgets\stream\StreamEntryWidget;
use humhub\modules\content\widgets\stream\WallStreamEntryOptions;
use humhub\widgets\LinkPager;

/* @var $resultSet \humhub\modules\content\search\ResultSet|null */
/* @var $searchRequest \humhub\modules\content\search\SearchRequest */

CardsAsset::register($this);
?>
<div class="container" data-action-component="stream.SimpleStream">
    <div class="panel panel-default">
        <div class="panel-heading">
            <?= Yii::t('ContentModule.search', '<strong>Search</strong>'); ?>
        </div>

        <div class="panel-body">
            <?= SearchFilters::widget(); ?>
        </div>

    </div>

    <?php if ($resultSet === null): ?>
        <!-- No Search -->
    <?php elseif (count($resultSet->results) === 0): ?>
        <div class="row cards">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <strong><?= Yii::t('ContentModule.search', 'No results found!'); ?></strong><br/>
                        <?= Yii::t('ContentModule.search', 'Try other keywords or remove filters.'); ?>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($resultSet->results as $result): ?>
            <?= StreamEntryWidget::renderStreamEntry($result->getModel(),
                (new WallStreamEntryOptions())->viewContext(WallStreamEntryOptions::VIEW_CONTEXT_SEARCH)) ?>
        <?php endforeach; ?>

        <div class="pagination-container">
            <?= LinkPager::widget(['pagination' => $resultSet->pagination]) ?>
        </div>
    <?php endif; ?>
</div>



