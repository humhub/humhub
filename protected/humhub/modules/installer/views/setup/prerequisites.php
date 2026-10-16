<?php

use humhub\helpers\Html;
use humhub\modules\admin\widgets\PrerequisitesList;
use humhub\widgets\Icon;

?>
<div class="panel panel-default animated fadeIn">

    <div class="panel-heading">
        <?php echo Yii::t('InstallerModule.base', '<strong>System</strong> Check'); ?>
    </div>

    <div class="panel-body">
        <p><?php echo Yii::t('InstallerModule.base', 'This overview shows all system requirements of HumHub.'); ?></p>

        <hr/>
        <?= PrerequisitesList::widget(); ?>

        <?php if (!$hasError): ?>
            <div class="alert alert-success">
                <?php echo Yii::t('InstallerModule.base', 'Congratulations! Everything is ok and ready to start over!'); ?>
            </div>
        <?php endif; ?>

        <hr>

        <?php echo Html::a(Icon::get('rotate-clockwise') . ' ' . Yii::t('InstallerModule.base', 'Check again'), ['/installer/setup/prerequisites'], ['class' => 'btn btn-primary', 'data-ui-loader' => '']); ?>

        <?php if (!$hasError): ?>
            <?php echo Html::a(Yii::t('InstallerModule.base', 'Next') . ' ' . Icon::get('circle-arrow-right'), ['/installer/setup/database'], ['class' => 'btn btn-primary', 'data-ui-loader' => '']); ?>
        <?php endif; ?>

    </div>
</div>
