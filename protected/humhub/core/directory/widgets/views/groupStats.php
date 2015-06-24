<?php
/**
 * Used by GroupStatisticsWidget to display statistics in the sidebar.
 *
 * @package humhub.modules_core.directory.widgets.views
 * @since 0.5
 */
?>


<div class="panel panel-default" id="groups-statistics-panel">

    <!-- Display panel menu widget -->
    <?php $this->widget('application.widgets.PanelMenuWidget', array('id' => 'groups-statistics-panel')); ?>

    <div class="panel-heading">
        <?php echo Yii::t('DirectoryModule.widgets_views_groupStats', '<strong>Group</strong> stats'); ?>
    </div>
    <div class="panel-body">
        <div class="knob-container" style="text-align: center; opacity: 0;">
            <strong><?php echo Yii::t('DirectoryModule.widgets_views_groupStats', 'Total groups'); ?></strong><br><br>

            <input id="groups-total" class="knob" data-width="120" data-displayPrevious="true" data-readOnly="true"
                   data-fgcolor="#708fa0" data-skin="tron"
                   data-thickness=".2" value="<?php echo $statsTotalGroups; ?>"
                   data-max="<?php echo $statsTotalGroups; ?>"
                   style="font-size: 25px !important; margin-top: 44px !important;">
        </div>

        <hr>

        <div class="knob-container" style="text-align: center; opacity: 0;">
            <strong><?php echo Yii::t('DirectoryModule.widgets_views_groupStats', 'Average members'); ?></strong><br><br>

            <input id="group-average" class="knob" data-width="120" data-displayPrevious="true" data-readOnly="true"
                   data-fgcolor="#708fa0"
                   data-skin="tron"
                   data-thickness=".2" value="<?php echo $statsAvgMembers; ?>"
                   data-max="<?php echo $statsTotalUsers; ?>"
                   style="font-size: 25px !important; margin-top: 44px !important;">
        </div>
        <hr>

        <div style="text-align: center;">
            <strong><?php echo Yii::t('DirectoryModule.widgets_views_groupStats', 'Top Group'); ?>:</strong> <?php echo CHtml::encode($statsTopGroup->name); ?>
        </div>
    </div>
</div>

<script>
    $(function () {
        $(".knob").knob();
        $(".knob-container").css( "opacity", 1 );
    });
</script>