<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\assets\CardsAsset;
use humhub\modules\content\widgets\SearchFilters;
use humhub\widgets\PageHeader;
use yii\helpers\Url;

CardsAsset::register($this);
?>
<div class="container" data-action-component="stream.SimpleStream" data-ui-init>
    <div class="d-flex flex-column gap-3">
        <?= PageHeader::title(Yii::t('ContentModule.search', 'Search')) ?>
        <div class="panel panel-default">
            <div class="panel-body">
                <?= SearchFilters::widget(['data' => ['action-url' => Url::to(['/content/search/results'])]]) ?>
            </div>
        </div>
    </div>

    <div data-stream-content></div>
</div>
