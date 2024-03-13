<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
use humhub\modules\content\search\ResultSet;
use humhub\modules\content\search\SearchRequest;
use humhub\modules\content\widgets\stream\StreamEntryWidget;
use humhub\modules\content\widgets\stream\WallStreamEntryOptions;
use humhub\widgets\AjaxLinkPager;

/* @var $resultSet ResultSet|null */
/* @var $searchRequest SearchRequest */

$hasResults = $resultSet !== null && count($resultSet->results);
?>
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
    <div class="search-results-header">
        <?= Yii::t('ContentModule.search', 'Results ({count})', ['count' => $resultSet->pagination->totalCount]) ?>
    </div>
    <div class="search-results">
    <?php foreach ($resultSet->results as $result): ?>
        <?= StreamEntryWidget::renderStreamEntry($result->getModel(),
            (new WallStreamEntryOptions())->viewContext(WallStreamEntryOptions::VIEW_CONTEXT_SEARCH)) ?>
    <?php endforeach; ?>
    </div>
    <div class="pagination-container">
        <?= AjaxLinkPager::widget([
            'pagination' => $resultSet->pagination,
            'linkOptions' => ['data' => ['action-click' => 'switchPage']]
        ]) ?>
    </div>
<?php endif; ?>

<?php if ($hasResults && $searchRequest->keyword !== '') : ?>
<script <?= Html::nonce() ?>>
$('.search-results [data-ui-widget]').on('afterInit', function() {
    if ($(this).data('isHighlighted')) {
        return;
    }
    $(this).data('isHighlighted', true);
<?php foreach (explode(' ', $searchRequest->keyword) as $keyword) : ?>
    $(this).highlight('<?= Html::encode($keyword) ?>');
<?php endforeach; ?>
});
</script>
<?php endif; ?>
