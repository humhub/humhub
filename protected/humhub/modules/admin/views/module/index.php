<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\assets\DirectoryAsset;
use humhub\modules\admin\assets\ModuleAsset;
use humhub\modules\admin\widgets\ModuleFilters;
use humhub\modules\admin\widgets\Modules;
use humhub\modules\ui\view\components\View;

/* @var $this View */

ModuleAsset::register($this);
DirectoryAsset::register($this);
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= Yii::t('AdminModule.base', '<strong>Module </strong> Administration'); ?>
    </div>
    <div class="panel-body">
        <?= ModuleFilters::widget(); ?>
    </div>
</div>

<?= Modules::widget() ?>