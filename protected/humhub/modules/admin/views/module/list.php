<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\assets\CardsAsset;
use humhub\libs\Html;
use humhub\modules\admin\assets\ModuleAsset;
use humhub\modules\admin\widgets\ModuleFilters;
use humhub\modules\admin\widgets\Modules;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\ui\view\components\View;

/* @var $this View */

ModuleAsset::register($this);
CardsAsset::register($this);
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= Yii::t('AdminModule.base', '<strong>Module </strong> Administration'); ?>
        <?= Html::a(Icon::get('cog'), ['/admin/setting'], ['class' => 'module-settings-link']) ?>
    </div>
    <div class="panel-body">
        <?= ModuleFilters::widget(); ?>
    </div>
</div>

<?= Modules::widget() ?>