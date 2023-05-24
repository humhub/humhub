<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\Module;
use humhub\modules\admin\widgets\ModuleCard;

/* @var string $type */
/* @var string|bool $title */
/* @var int $count */
/* @var string $noModulesMessage */
/* @var string $view */
/* @var string $moduleTemplate */
/* @var Module[] $modules */
?>
<div class="modules-group">
    <?php if ($title !== false) : ?>
        <strong><?= $title ?> (<span class="group-modules-count-<?= $type ?>"><?= $count ?></span>) :</strong>
    <?php endif; ?>

    <?php if (empty($modules)) : ?>
        <div class="col-md-12">
            <?php if ($count) : ?>
                <strong><?= Yii::t('AdminModule.modules', 'No modules installed.') ?></strong><br/>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php foreach ($modules as $module) : ?>
        <?= ModuleCard::widget([
            'module' => $module,
            'view' => $view ?? null,
            'template' => $moduleTemplate ?? null,
        ]); ?>
    <?php endforeach; ?>
</div>
