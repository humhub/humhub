<?php
/**
 * Used by GroupStatisticsWidget to display statistics in the sidebar.
 *
 * @package humhub.modules_core.directory.widgets.views
 * @since 0.5
 */
?>


<div class="panel panel-default">
    <div class="panel-heading">
        <?php echo Yii::t('DirectoryModule.base', 'Statistics'); ?>
    </div>
    <div class="panel-body">
        <div style="text-align: center;">
            <strong><?php echo Yii::t('DirectoryModule.base', 'Total groups'); ?></strong><br><br>

            <input id="groups-total" class="knob" data-width="120" data-displayPrevious="true" data-readOnly="true"
                   data-fgcolor="#7191a8" data-skin="tron"
                   data-thickness=".2" value="0"
                   data-max="<?php echo $statsTotalGroups; ?>"
                   style="width: 75px; position: absolute; margin-top: 53.57142857142857px; margin-left: -112.5px; font-size: 37.5px; border: none; background-image: none; font-family: Arial; font-weight: bold; text-align: center; color: rgb(255, 236, 3); padding: 0px; -webkit-appearance: none; background-position: initial initial; background-repeat: initial initial;">
            <script type="text/javascript">
                $(document).ready(function () {
                    // animate stats
                    animateKnob('#groups-total', <?php echo $statsTotalGroups; ?>);
                });
            </script>
        </div>

        <hr>

        <div style="text-align: center;">
            <strong><?php echo Yii::t('DirectoryModule.base', 'Average members'); ?></strong><br><br>

            <input id="group-average" class="knob" data-width="120" data-displayPrevious="true" data-readOnly="true"
                   data-fgcolor="#7191a8"
                   data-skin="tron"
                   data-thickness=".2" value="0"
                   data-max="<?php echo $statsTotalUsers; ?>"
                   style="width: 75px; position: absolute; margin-top: 53.57142857142857px; margin-left: -112.5px; font-size: 37.5px; border: none; background-image: none; font-family: Arial; font-weight: bold; text-align: center; color: rgb(255, 236, 3); padding: 0px; -webkit-appearance: none; background-position: initial initial; background-repeat: initial initial;">
        </div>
        <script type="text/javascript">
            $(document).ready(function () {
                // animate stats
                animateKnob('#group-average', <?php echo $statsAvgMembers; ?>);
            });
        </script>

        <hr>

        <div style="text-align: center;">
            <strong><?php echo Yii::t('DirectoryModule.base', 'Top Group'); ?>
                :</strong> <?php echo $statsTopGroup->name; ?>
        </div>
    </div>
</div>

<script>
    $(function () {
        $(".knob").knob();
    });


    //animate the stats
    function animateKnob(id, value) {

        $(id).animate({value: value}, {
            duration: 1000,
            easing: 'swing',
            step: function () {
                $(id).val(Math.round(this.value)).trigger('change');
            }
        })
    }


</script>