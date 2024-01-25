<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\admin\widgets\AvailableModuleUpdatesInfo;
use humhub\modules\admin\widgets\InstalledModuleList;
use humhub\modules\marketplace\widgets\MarketplaceLink;

?>
<div class="card card-default">
    <div class="card-header">
        <?= Yii::t('AdminModule.base', '<strong>Module</strong> administration') ?>
        <?= MarketplaceLink::info(Yii::t('AdminModule.base', 'Add more modules'))
            ->icon('external-link')
            ->right()->sm() ?>
    </div>
    <div class="card-body">
        <div class="form-text">
            <?= Yii::t('AdminModule.base', 'This overview shows you all installed modules and allows you to enable, disable, configure and of course uninstall them. To discover new modules, take a look into our Marketplace. Please note that deactivating or uninstalling a module will result in the loss of any content that was created with that module.') ?>
        </div>

        <?php if (!Yii::$app->user->can(ManageModules::class)) : ?>
            <div class="alert alert-info">
                <?= Yii::t('AdminModule.base', 'You do not have the permission to manage modules. Please contact the administrator for further information.') ?>
            </div>
        <?php endif; ?>

        <?php if (!Yii::$app->user->can(ManageSettings::class)) : ?>
            <div class="alert alert-info">
                <?= Yii::t('AdminModule.base', 'You do not have the permission to configure modules. Please contact the administrator for further information.') ?>
            </div>
        <?php endif; ?>

        <?= AvailableModuleUpdatesInfo::widget() ?>
        <?= InstalledModuleList::widget() ?>
        <?= MarketplaceLink::primary(Yii::t('AdminModule.base', 'Visit Marketplace'))
            ->icon('external-link') ?>
    </div>
</div>
