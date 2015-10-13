<?php
/**
 * Used by SpaceStatisticsWidget to display statistics in the sidebar.
 *
 * @package humhub.modules_core.directory.views
 * @since 0.5
 */
?>
<div class="panel panel-default" id="spaces-statistics-panel">

    <!-- Display panel menu widget -->
    <?php $this->widget('application.widgets.PanelMenuWidget', array('id' => 'spaces-statistics-panel')); ?>

    <div class="panel-heading">
        <?php echo Yii::t('DirectoryModule.widgets_views_spaceStats', '<strong>Space</strong> stats'); ?>
    </div>

    <div class="panel-body">
        <div class="knob-container" style="text-align: center; opacity: 0;">
            <strong><?php echo Yii::t('DirectoryModule.widgets_views_spaceStats', 'Total spaces'); ?></strong><br><br>

            <input id="spaces-total" class="knob" data-width="120" data-displayprevious="true" data-readOnly="true"
                   data-fgcolor="#708fa0" data-skin="tron"
                   data-thickness=".2" value="<?php echo $statsCountSpaces; ?>"
                   data-max="<?php echo $statsCountSpaces; ?>"
                   style="font-size: 25px !important; margin-top: 44px !important;">
        </div>

        <hr>

        <div class="knob-container" style="text-align: center; opacity: 0;">
            <strong><?php echo Yii::t('DirectoryModule.widgets_views_spaceStats', 'Private spaces'); ?></strong><br><br>

            <input id="spaces-private" class="knob" data-width="120" data-displayprevious="true" data-readOnly="true"
                   data-fgcolor="#708fa0"
                   data-skin="tron"
                   data-thickness=".2" value="<?php echo $statsCountSpacesHidden; ?>"
                   data-max="<?php echo $statsCountSpaces; ?>"
                   style="font-size: 25px !important; margin-top: 44px !important;">
        </div>
        <hr>

        <?php if (isset($statsSpaceMostMembers->name)) { ?>
            <div style="text-align: center;">
                <strong><?php echo Yii::t('DirectoryModule.widgets_views_spaceStats', 'Most members'); ?>:
                </strong> <?php echo CHtml::encode($statsSpaceMostMembers->name); ?>
            </div>
        <?php } ?>
    </div>
</div>

<script>
    $(function() {
        $(".knob").knob();
        $(".knob-container").css( "opacity", 1 );
    });
</script>