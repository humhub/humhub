<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\SearchProvider;
use humhub\libs\Html;
use humhub\widgets\Button;

/* @var array $options */
/* @var SearchProvider $searchProvider */
?>
<?= Html::beginTag('li', $options) ?>
    <div class="dropdown-search-provider-title">
        <?= $searchProvider->getName() ?>
        <?php if ($searchProvider->isSearched()) : ?>
            <?= Html::tag('span', '(' . $searchProvider->getTotal() . ')') ?>
        <?php endif; ?>
    </div>
    <div class="dropdown-search-provider-content">
        <?php if ($searchProvider->isSearched()) : ?>
            <?php if ($searchProvider->hasRecords()) : ?>
                <?php foreach ($searchProvider->getRecords() as $record) : ?>
                    <a href="<?= $record->getUrl() ?>" class="dropdown-search-provider-content-item">
                        <span><?= $record->getImage() ?></span>
                        <span>
                            <?= Html::encode($record->getTitle()) ?>
                            <span><?= Html::encode($record->getDescription()) ?></span>
                        </span>
                    </a>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="dropdown-search-provider-no-results"><?= Yii::t('base', 'No results') ?></div>
            <?php endif; ?>
            <?= Button::defaultType($searchProvider->getAllResultsText())
                ->link($searchProvider->getAllResultsUrl())
                ->cssClass('dropdown-search-provider-show-all')
                ->loader(false) ?>
        <?php endif; ?>
    </div>
    <div class="clearfix"></div>
<?= Html::endTag('li') ?>
