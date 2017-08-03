<?php

use yii\helpers\Html;

humhub\modules\directory\assets\DirectoryAsset::register($this);
?>

<div class="panel panel-default" id="groups-statistics-panel">

    <!-- Display panel menu widget -->
    <?= humhub\widgets\PanelMenu::widget(['id' => 'groups-statistics-panel']); ?>

    <div class="panel-heading">
        <?= Yii::t('DirectoryModule.base', '<strong>Group</strong> stats'); ?>
    </div>
    <div class="panel-body">
        <div class="knob-container" style="text-align: center; opacity: 0;">
            <strong><?= Yii::t('DirectoryModule.base', 'Total groups'); ?></strong><br><br>

            <input id="groups-total" class="knob" data-width="120" data-height="140" data-displayPrevious="true" data-readOnly="true"
                   data-fgcolor="<?= $this->theme->variable('primary'); ?>" data-skin="tron"
                   data-thickness=".2" value="<?= $statsTotalGroups; ?>"
                   data-max="<?= $statsTotalGroups; ?>"
                   style="font-size: 25px !important; margin-top: 44px !important;">
        </div>

        <hr>

        <div class="knob-container" style="text-align: center; opacity: 0;">
            <strong><?= Yii::t('DirectoryModule.base', 'Average members'); ?></strong><br><br>

            <input id="group-average" class="knob" data-width="120" data-height="140" data-displayPrevious="true" data-readOnly="true"
                   data-fgcolor="<?= $this->theme->variable('primary'); ?>"
                   data-skin="tron"
                   data-thickness=".2" value="<?= $statsAvgMembers; ?>"
                   data-max="<?= $statsTotalUsers; ?>"
                   style="font-size: 25px !important; margin-top: 44px !important;">
        </div>

        <hr>

        <div style="text-align: center;">
            <strong><?= Yii::t('DirectoryModule.base', 'Top Group'); ?>:</strong> <?= Html::encode($statsTopGroup->name); ?>
        </div>
    </div>
</div>
