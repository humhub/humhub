<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/* @var $results string|null */
/* @var $totalCount int */
?>
<?php if ($results === '') : ?>
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
<?php elseif ($results !== null) : ?>
    <div class="search-results-header">
        <?= Yii::t('ContentModule.search', 'Results ({count})', ['count' => $totalCount]) ?>
    </div>
    <div class="search-results" data-stream-action-form-results>
        <?= $results ?>
    </div>
<?php endif; ?>
