<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\assets\CardsAsset;
use humhub\modules\marketplace\assets\Assets;
use humhub\modules\marketplace\widgets\ModuleFilters;
use humhub\modules\marketplace\widgets\ModuleGroups;
use humhub\modules\marketplace\widgets\Settings;
use humhub\modules\ui\view\components\View;

/* @var $this View */

CardsAsset::register($this);
Assets::register($this);
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <strong><?= Yii::t('MarketplaceModule.base', 'Marketplace') ?></strong>
        <?= Settings::widget() ?>
    </div>
    <div class="panel-body">
        <?= ModuleFilters::widget(); ?>
    </div>
</div>

<?= ModuleGroups::widget() ?>
