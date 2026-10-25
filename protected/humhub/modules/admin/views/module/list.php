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
use humhub\modules\admin\widgets\ModulesHeaderActions;
use humhub\modules\marketplace\widgets\MarketplaceLink;
use humhub\widgets\PageHeader;

?>
<div class="d-flex flex-column gap-3">
    <?= PageHeader::title(Yii::t('AdminModule.base', 'Modules'))->actions(ModulesHeaderActions::class) ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="text-body-secondary">
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
</div>
