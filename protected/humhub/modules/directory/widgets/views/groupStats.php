<?php

use yii\helpers\Html;
?>

<div class="panel panel-default" id="groups-statistics-panel">

    <!-- Display panel menu widget -->
    <?= \humhub\widgets\PanelMenu::widget(array('id' => 'groups-statistics-panel')); ?>

    <div class="panel-heading">
        <?= Yii::t('DirectoryModule.widgets_views_groupStats', '<strong>Group</strong> stats'); ?>
    </div>
    <div class="panel-body">
        <div class="knob-container" style="text-align: center; opacity: 0;">
            <strong><?= Yii::t('DirectoryModule.widgets_views_groupStats', 'Total groups'); ?></strong><br><br>

            <input id="groups-total" class="knob" data-width="120" data-height="140" data-displayPrevious="true" data-readOnly="true"
                   data-fgcolor="<?= Yii::$app->settings->get('colorPrimary'); ?>" data-skin="tron"
                   data-thickness=".2" value="<?= $statsTotalGroups; ?>"
                   data-max="<?= $statsTotalGroups; ?>"
                   style="font-size: 25px !important; margin-top: 44px !important;">
        </div>

        <hr>

        <div class="knob-container" style="text-align: center; opacity: 0;">
            <strong><?= Yii::t('DirectoryModule.widgets_views_groupStats', 'Average members'); ?></strong><br><br>

            <input id="group-average" class="knob" data-width="120" data-height="140" data-displayPrevious="true" data-readOnly="true"
                   data-fgcolor="<?= Yii::$app->settings->get('colorPrimary'); ?>"
                   data-skin="tron"
                   data-thickness=".2" value="<?= $statsAvgMembers; ?>"
                   data-max="<?= $statsTotalUsers; ?>"
                   style="font-size: 25px !important; margin-top: 44px !important;">
        </div>
        <hr>

        <div style="text-align: center;">
            <strong><?= Yii::t('DirectoryModule.widgets_views_groupStats', 'Top Group'); ?>:</strong> <?= Html::encode($statsTopGroup->name); ?>
        </div>
    </div>
</div>

<script>
$(function () {
    $(".knob").knob();
    $(".knob-container").css("opacity", 1);
});
</script>