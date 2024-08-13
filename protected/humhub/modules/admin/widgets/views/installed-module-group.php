<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\Module;
use yii\web\View;

/* @var View $this */
/* @var string $type */
/* @var string $title */
/* @var int $count */
/* @var Module[] $modules */
?>
<div class="modules-group">
    <strong><?= $title ?> (<span class="group-modules-count-<?= $type ?>"><?= $count ?></span>):</strong>

    <?php foreach ($modules as $module) : ?>
        <?= $this->render('installed-module', ['module' => $module]) ?>
    <?php endforeach; ?>
</div>
