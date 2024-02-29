<?php

use humhub\assets\CardsAsset;
use humhub\libs\Html;
use humhub\modules\content\search\ResultSet;
use humhub\modules\content\search\SearchRequest;
use humhub\modules\content\widgets\SearchFilters;
use humhub\modules\content\widgets\stream\StreamEntryWidget;
use humhub\modules\content\widgets\stream\WallStreamEntryOptions;
use humhub\widgets\LinkPager;

/* @var $resultSet ResultSet|null */
/* @var $searchRequest SearchRequest */

CardsAsset::register($this);

$hasResults = $resultSet !== null && count($resultSet->results);
?>
<div class="container" data-action-component="stream.SimpleStream">
    <div class="panel panel-default">
        <div class="panel-heading">
            <strong><?= Yii::t('ContentModule.search', 'Search') ?></strong>
        </div>

        <div class="panel-body">
            <?= SearchFilters::widget(); ?>
        </div>

    </div>

    <?php if (!$hasResults && $resultSet !== null): ?>
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
    <?php endif; ?>

    <?php if ($hasResults): ?>
        <div class="search-results">
        <?php foreach ($resultSet->results as $result): ?>
            <?= StreamEntryWidget::renderStreamEntry($result->getModel(),
                (new WallStreamEntryOptions())->viewContext(WallStreamEntryOptions::VIEW_CONTEXT_SEARCH)) ?>
        <?php endforeach; ?>
        </div>
        <div class="pagination-container">
            <?= LinkPager::widget(['pagination' => $resultSet->pagination]) ?>
        </div>
    <?php endif; ?>
</div>

<?php if ($hasResults && $searchRequest->keyword !== '') : ?>
<script <?= Html::nonce() ?>>
$(document).on('humhub:ready', function() {
<?php foreach (explode(' ', $searchRequest->keyword) as $keyword) : ?>
    $('.search-results').highlight('<?= Html::encode($keyword) ?>');
<?php endforeach; ?>
});
</script>
<?php endif; ?>
