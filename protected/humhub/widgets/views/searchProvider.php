<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\interfaces\SearchProviderInterface;
use humhub\libs\Html;
use humhub\widgets\Button;

/* @var array $options */
/* @var SearchProviderInterface $searchProvider */
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
            <?php foreach ($searchProvider->getRecords() as $record) : ?>
                <a href="<?= $record->getUrl() ?>" class="dropdown-search-provider-content-item">
                    <span><?= $record->getImage() ?></span>
                    <span>
                        <?= Html::encode($record->getTitle()) ?>
                        <span><?= Html::encode($record->getDescription()) ?></span>
                    </span>
                </a>
            <?php endforeach; ?>
            <?= Button::defaultType(Yii::t('base', 'Show all results'))
                ->link($searchProvider->getAllResultsUrl())
                ->cssClass('dropdown-search-provider-show-all')
                ->loader(false) ?>
        <?php endif; ?>
    </div>
    <div class="clearfix"></div>
<?= Html::endTag('li') ?>
