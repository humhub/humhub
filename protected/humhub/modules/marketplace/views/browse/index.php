<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\assets\CardsAsset;
use humhub\modules\marketplace\widgets\ModuleFilters;
use humhub\modules\marketplace\widgets\Modules;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\ui\view\components\View;
use humhub\widgets\Button;

/* @var $this View */

CardsAsset::register($this);
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <strong><?= Yii::t('MarketplaceModule.base', 'Marketplace') ?></strong>
        <?= Button::asLink(Icon::get('cog'))
            ->action('ui.modal.load', ['/marketplace/browse/module-settings'])
            ->cssClass('module-settings-icon')
            ->tooltip(Yii::t('MarketplaceModule.base', 'Settings')) ?>
    </div>
    <div class="panel-body">
        <?= ModuleFilters::widget(); ?>
    </div>
</div>

<?= Modules::widget() ?>
