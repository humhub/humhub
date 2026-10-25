<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\assets\CardsAsset;
use humhub\modules\content\widgets\SearchFilters;
use humhub\widgets\ContentCard;
use humhub\widgets\PageHeader;
use yii\helpers\Url;

CardsAsset::register($this);
?>
<div class="container" data-action-component="stream.SimpleStream" data-ui-init>
    <div class="d-flex flex-column gap-3">
        <?= PageHeader::title(Yii::t('ContentModule.search', 'Search')) ?>
        <?php ContentCard::begin() ?>
            <?= SearchFilters::widget(['data' => ['action-url' => Url::to(['/content/search/results'])]]) ?>
        <?php ContentCard::end() ?>
    </div>

    <div data-stream-content></div>
</div>
