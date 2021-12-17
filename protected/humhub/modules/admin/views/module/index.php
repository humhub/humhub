<?php

use humhub\components\Module;
use humhub\modules\admin\assets\ModuleAsset;
use humhub\modules\admin\widgets\ModuleCard;
use humhub\modules\admin\widgets\ModuleFilters;
use humhub\modules\ui\view\components\View;

/* @var $this View */
/* @var $filteredInstalledModules Module[] */
/* @var $installedModulesCount int */

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
    <?php if (empty($filteredInstalledModules)): ?>
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <?php if ($installedModulesCount) : ?>
                        <strong><?= Yii::t('AdminModule.base', 'No modules found.'); ?></strong><br/>
                        <?= Yii::t('AdminModule.base', 'Try other keywords or remove filters.'); ?>
                    <?php else : ?>
                        <strong><?= Yii::t('AdminModule.base', 'No modules installed yet. Install some to enhance the functionality!'); ?></strong>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php foreach ($filteredInstalledModules as $module) : ?>
        <?= ModuleCard::widget(['module' => $module]); ?>
    <?php endforeach; ?>
</div>