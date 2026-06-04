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
    <div class="row cards" aria-live="polite" aria-atomic="false">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <p role="status" aria-live="polite" class="m-0">
                        <strong><?= Yii::t('ContentModule.search', 'No results found!') ?></strong><br>
                        <?= Yii::t('ContentModule.search', 'Try other keywords or remove filters.') ?>
                    </p>
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
