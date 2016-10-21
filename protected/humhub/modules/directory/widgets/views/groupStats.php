<?php

use yii\helpers\Html;

?>


<div class="panel panel-default" id="groups-statistics-panel">

    <!-- Display panel menu widget -->
    <?php echo humhub\widgets\PanelMenu::widget(array('id' => 'groups-statistics-panel')); ?>

    <div class="panel-heading">
        <?php echo Yii::t('DirectoryModule.widgets_views_groupStats', '<strong>Group</strong> stats'); ?>
    </div>
    <div class="panel-body">
        <div class="knob-container" style="text-align: center; opacity: 0;">
            <strong><?php echo Yii::t('DirectoryModule.widgets_views_groupStats', 'Total groups'); ?></strong><br><br>

            <input id="groups-total" class="knob" data-width="120" data-height="140" data-displayPrevious="true" data-readOnly="true"
                   data-fgcolor="<?php echo Yii::$app->settings->get('colorPrimary'); ?>" data-skin="tron"
                   data-thickness=".2" value="<?php echo $statsTotalGroups; ?>"
                   data-max="<?php echo $statsTotalGroups; ?>"
                   style="font-size: 25px !important; margin-top: 44px !important;">
        </div>

        <hr>

        <div class="knob-container" style="text-align: center; opacity: 0;">
            <strong><?php echo Yii::t('DirectoryModule.widgets_views_groupStats', 'Average members'); ?></strong><br><br>

            <input id="group-average" class="knob" data-width="120" data-height="140" data-displayPrevious="true" data-readOnly="true"
                   data-fgcolor="<?php echo Yii::$app->settings->get('colorPrimary'); ?>"
                   data-skin="tron"
                   data-thickness=".2" value="<?php echo $statsAvgMembers; ?>"
                   data-max="<?php echo $statsTotalUsers; ?>"
                   style="font-size: 25px !important; margin-top: 44px !important;">
        </div>
        <hr>

        <div style="text-align: center;">
            <strong><?php echo Yii::t('DirectoryModule.widgets_views_groupStats', 'Top Group'); ?>:</strong> <?php echo Html::encode($statsTopGroup->name); ?>
        </div>
    </div>
</div>

<script>
    $(function () {
        $(".knob").knob();
        $(".knob-container").css("opacity", 1);
    });
</script>