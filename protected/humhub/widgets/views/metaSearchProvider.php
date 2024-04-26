<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\interfaces\MetaSearchProviderInterface;
use humhub\libs\Html;
use humhub\widgets\Button;

/* @var array $options */
/* @var MetaSearchProviderInterface $provider */
?>
<?= Html::beginTag('li', $options) ?>
    <div class="search-provider-title">
        <?= $provider->getName() ?>
        <?php if ($provider->getService()->isSearched()) : ?>
            <?= Html::tag('span', '(' . Yii::$app->formatter->asShortInteger($provider->getService()->getTotal()) . ')') ?>
        <?php endif; ?>
    </div>
    <div class="search-provider-content">
        <?php if ($provider->getService()->isSearched()) : ?>
            <?php if ($provider->getService()->hasResults()) : ?>
                <?php foreach ($provider->getService()->getResults() as $record) : ?>
                    <?= Html::beginTag('a', [
                        'href' => $record->getUrl(),
                        'class' => 'search-provider-record',
                        'target' => $provider->getService()->getLinkTarget($record->getUrl()),
                    ]) ?>
                        <span class="search-provider-record-image"><?= $record->getImage() ?></span>
                        <span class="search-provider-record-text">
                            <span><?= $record->getTitle() ?></span>
                            <span><?= $record->getDescription() ?></span>
                        </span>
                    <?= Html::endTag('a') ?>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="search-provider-no-results"><?= Yii::t('base', 'No results') ?></div>
            <?php endif; ?>
            <div class="search-provider-actions">
                <?= Button::defaultType($provider->getAllResultsText())
                    ->link($provider->getService()->getUrl())
                    ->cssClass('search-provider-show-all')
                    ->options(['target' => $provider->getService()->getLinkTarget()])
                    ->loader(false) ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="clearfix"></div>
<?= Html::endTag('li') ?>
