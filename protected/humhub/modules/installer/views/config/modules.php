<?php

use yii\widgets\ActiveForm;
use yii\bootstrap\Html;
?>
<div id="name-form" class="panel panel-default animated fadeIn">

    <div class="panel-heading">
        <?php echo Yii::t('InstallerModule.base', 'Recommended <strong>Modules</strong>'); ?>
    </div>

    <div class="panel-body">
        <?= Html::beginForm(); ?>

        <?php foreach ($modules as $module): ?>
            <?php echo Html::checkbox('enableModules[' . $module['id'] . ']', true); ?>
            <strong><?php echo $module['name']; ?></strong><br />
            <?php echo $module['description']; ?><br />
            <br />
        <?php endforeach; ?>

        <hr>
        <?php echo Html::submitButton(Yii::t('base', 'Next'), array('class' => 'btn btn-primary', 'data-loader' => "modal", 'data-message' => Yii::t('InstallerModule.base', 'Downloading & Installing Modules...'))); ?>

        <?= Html::endForm(); ?>

        <?php //echo Html::a(Yii::t('base', 'Next'), ['modules', 'ok' => 1], array('class' => 'btn btn-primary')); ?>
    </div>
</div>


