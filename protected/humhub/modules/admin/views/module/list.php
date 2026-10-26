<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\modules\admin\widgets\ModulesHeaderActions;
use humhub\modules\admin\widgets\ModuleTable;
use humhub\widgets\PageHeader;

/* @var $this View */
?>
<div class="d-flex flex-column gap-3">
    <?= PageHeader::title(Yii::t('AdminModule.base', 'Modules'))->actions(ModulesHeaderActions::class) ?>
    <?= ModuleTable::widget() ?>
</div>
