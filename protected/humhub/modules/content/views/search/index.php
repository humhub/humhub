<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\assets\CardsAsset;
use humhub\modules\content\widgets\SearchFilters;
use yii\helpers\Url;

CardsAsset::register($this);
?>
<div class="container" data-action-component="stream.SimpleStream" data-ui-init>
    <div class="panel panel-default">
        <div class="panel-heading">
            <strong><?= Yii::t('ContentModule.search', 'Search') ?></strong>
        </div>

        <div class="panel-body">
            <?= SearchFilters::widget(['data' => [
                'action-url' => Url::to(['/content/search/results']),
                'action-content' => '#content-search-body'
            ]]) ?>
        </div>
    </div>

    <div id="content-search-body"></div>
</div>
