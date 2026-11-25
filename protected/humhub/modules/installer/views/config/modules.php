<?php


use humhub\helpers\Html;
use humhub\widgets\bootstrap\Button;

/* @var array $modules */
?>
<div id="name-form" class="card content-card animated fadeIn">

    <div class="card-header">
        <div class="card-title"><?php echo Yii::t('InstallerModule.base', 'Recommended <strong>Modules</strong>'); ?></div>
    </div>

    <div class="card-body">

        <p><?php echo Yii::t('InstallerModule.base', 'HumHub is very flexible and can be adjusted and/or expanded for various different applications thanks to its’ different modules.  The following modules are just a few examples and the ones we thought are most important for your chosen application.<br><br>You can always install or remove modules later. You can find more available modules after installation in the admin area.'); ?></p>
        <br>

        <?= Html::beginForm(); ?>

        <?php foreach ($modules as $module): ?>
            <div class="mb-3">
                <div class="checkbox">
                    <label>
                        <?= Html::checkbox('enableModules[' . $module['id'] . ']', true) ?>
                        <?= $module['name'] ?>
                    </label>
                    <p class="text-body-secondary"><?= $module['description'] ?></p>
                </div>
            </div>
            <hr>
        <?php endforeach; ?>

        <?= Button::primary(Yii::t('InstallerModule.base', 'Next'))
            ->loader(Yii::t('InstallerModule.base', 'Downloading & Installing Modules...'))
            ->submit() ?>

        <?= Html::endForm(); ?>

    </div>
</div>
