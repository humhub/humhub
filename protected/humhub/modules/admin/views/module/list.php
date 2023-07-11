<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\admin\widgets\AvailableModuleUpdatesInfo;
use humhub\modules\admin\widgets\InstalledModuleList;
use humhub\modules\marketplace\widgets\MarketplaceLink;
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= Yii::t('AdminModule.base', '<strong>Module</strong> Configuration') ?>
        <?= MarketplaceLink::info(Yii::t('AdminModule.base', 'Add more modules'))
            ->icon('external-link')
            ->right()->sm() ?>
    </div>
    <div class="panel-body">
        <div class="help-block">
            <?= Yii::t('AdminModule.base', 'This overview shows you all installed modules and allows you to enable, disable, configure and of course uninstall them. To discover new modules, take a look into our Marketplace. Please note that deactivating or uninstalling a module will result in the loss of any content that was created with that module.') ?>
        </div>
        <?= AvailableModuleUpdatesInfo::widget() ?>
        <?= InstalledModuleList::widget() ?>
        <?= MarketplaceLink::primary(Yii::t('AdminModule.base', 'Visit Marketplace'))
            ->icon('external-link') ?>
    </div>
</div>
