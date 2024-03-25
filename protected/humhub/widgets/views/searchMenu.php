<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\assets\SearchAsset;
use humhub\components\SearchProvider;
use humhub\libs\Html;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\widgets\Button;
use humhub\widgets\Link;
use humhub\widgets\SearchProviderWidget;
use yii\web\View;

/* @var View $this */
/* @var array $options */
/* @var SearchProvider[] $searchProviders */

SearchAsset::register($this);
?>
<?= Html::beginTag('li', $options) ?>
    <?= Link::asLink('')
        ->icon('search')
        ->id('search-menu')
        ->action('menu')
        ->options(['data-toggle' => 'dropdown'])
        ->cssClass('dropdown-toggle') ?>
    <div id="dropdown-search" class="dropdown-menu">
        <div class="dropdown-header">
            <div class="arrow"></div>
            <?= Yii::t('base', 'Search') ?>
            <?= Icon::get('close', ['id' => 'dropdown-search-close']) ?>
        </div>
        <div class="dropdown-search-form">
            <?= Button::defaultType()
                ->icon('search')
                ->action('search')
                ->cssClass('dropdown-search-button')
                ->loader(false) ?>
            <?= Html::input('text', 'keyword', '', [
                'class' => 'dropdown-search-keyword form-control',
                'autocomplete' => 'off'
            ]) ?>
        </div>
        <ul class="dropdown-search-list">
            <?php foreach ($searchProviders as $searchProvider) : ?>
                <?= SearchProviderWidget::widget(['searchProvider' => $searchProvider]) ?>
            <?php endforeach; ?>
        </ul>
    </div>
<?= Html::endTag('li') ?>
