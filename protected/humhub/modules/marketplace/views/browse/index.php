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
        <div class="help-block">
            <?= Yii::t('MarketplaceModule.base', 'Find all the modules you can add to your network in our HumHub Marketplace. Discover numerous add-ons and features that customize the software and give you the possibility to configure your network to your needs.') ?>
            <br><br>
            <?= Yii::t('MarketplaceModule.base', 'After installing the required module, all you have to do is activate it. After that you can instantly start using the module or function. Please note that some modules need to be configured before use.') ?>
        </div>
        <?= ModuleFilters::widget(); ?>
    </div>
</div>

<?= ModuleGroups::widget() ?>
