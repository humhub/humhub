<?php

use humhub\modules\admin\assets\ModuleAsset;
use humhub\modules\admin\widgets\ModuleCard;
use humhub\modules\admin\widgets\ModuleFilters;
use humhub\modules\ui\view\components\View;

/* @var $this View */
/* @var $installedModules array */
/* @var $installedModulesCount int */
/* @var $deprecatedModuleIds array */
/* @var $marketplaceUrls array */

ModuleAsset::register($this);
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= Yii::t('AdminModule.base', '<strong>Module </strong> Administration'); ?>
    </div>
    <div class="panel-body">
        <?= ModuleFilters::widget(); ?>
    </div>
    <?php /*
    <div class="panel-footer">
        A new HumHub update is available. Install it now to keep your network up to date and to have access to the latest module versions.
    </div> */ ?>
</div>

<h4 class="modules-type"><?= Yii::t('AdminModule.modules', 'Installed ({installedCount})', ['installedCount' => $installedModulesCount]) ?></h4>

<div class="row cards">
    <?php if (!$installedModulesCount): ?>
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <strong><?= Yii::t('AdminModule.base', 'No modules installed yet. Install some to enhance the functionality!'); ?></strong><br/>
                    <?= Yii::t('AdminModule.base', 'Try other keywords or remove filters.'); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php foreach ($installedModules as $module) : ?>
        <?= ModuleCard::widget(['module' => $module]); ?>
    <?php endforeach; ?>
</div>