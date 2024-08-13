<?php


use yii\bootstrap\Html;

/* @var array $modules */
?>
<div id="name-form" class="panel panel-default animated fadeIn">

    <div class="panel-heading">
        <?php echo Yii::t('InstallerModule.base', 'Recommended <strong>Modules</strong>'); ?>
    </div>

    <div class="panel-body">

        <p><?php echo Yii::t('InstallerModule.base', 'HumHub is very flexible and can be adjusted and/or expanded for various different applications thanks to its’ different modules.  The following modules are just a few examples and the ones we thought are most important for your chosen application.<br><br>You can always install or remove modules later. You can find more available modules after installation in the admin area.'); ?></p>
        <br>

        <?= Html::beginForm(); ?>

        <?php foreach ($modules as $module): ?>
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        <?= Html::checkbox('enableModules[' . $module['id'] . ']', true) ?>
                        <?= $module['name'] ?>
                    </label>
                    <p class="help-block"><?= $module['description'] ?></p>
                </div>
            </div>
            <hr>
        <?php endforeach; ?>

        <?php echo Html::submitButton(Yii::t('base', 'Next'), ['class' => 'btn btn-primary', 'data-loader' => "modal", 'data-message' => Yii::t('InstallerModule.base', 'Downloading & Installing Modules...')]); ?>

        <?= Html::endForm(); ?>

    </div>
</div>


