<?php
/**
 * Used by SpaceStatisticsWidget to display statistics in the sidebar.
 *
 * @package humhub.modules_core.directory.views
 * @since 0.5
 */
?>

<div class="panel panel-default spaces" id="new-spaces-panel">

    <!-- Display panel menu widget -->
    <?php $this->widget('application.widgets.PanelMenuWidget', array('id' => 'new-spaces-panel')); ?>

    <div class="panel-heading">
        <?php echo Yii::t('DirectoryModule.widgets_views_spaceStats', '<strong>New</strong> spaces'); ?>
    </div>
    <div class="panel-body">
        <?php foreach ($newSpaces as $space) : ?>
            <a href="<?php echo $space->getUrl(); ?>">
                <img src="<?php echo $space->getProfileImage()->getUrl(); ?>" class="img-rounded tt img_margin"
                     height="40" width="40" alt="40x40" data-src="holder.js/40x40"
                     style="width: 40px; height: 40px;"
                     data-toggle="tooltip" data-placement="top" title=""
                     data-original-title="<strong><?php echo $space->name; ?></strong>">
            </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="panel panel-default" id="spaces-statistics-panel">

    <!-- Display panel menu widget -->
    <?php $this->widget('application.widgets.PanelMenuWidget', array('id' => 'spaces-statistics-panel')); ?>

    <div class="panel-heading">
        <?php echo Yii::t('DirectoryModule.widgets_views_spaceStats', '<strong>Space</strong> stats'); ?>
    </div>

    <div class="panel-body">
        <div style="text-align: center;">
            <strong><?php echo Yii::t('DirectoryModule.widgets_views_spaceStats', 'Total spaces'); ?></strong><br><br>

            <input id="spaces-total" class="knob" data-width="120" data-displayprevious="true" data-readOnly="true"
                   data-fgcolor="#7191a8" data-skin="tron"
                   data-thickness=".2" value="0"
                   data-max="<?php echo $statsCountSpaces; ?>"
                   style="width: 75px; position: absolute; margin-top: 53.57142857142857px; margin-left: -112.5px; font-size: 37.5px; border: none; background-image: none; font-family: Arial; font-weight: bold; text-align: center; color: rgb(255, 236, 3); padding: 0px; -webkit-appearance: none; background-position: initial initial; background-repeat: initial initial;">
            <script type="text/javascript">
                $(document).ready(function() {
                    // animate stats
                    animateKnob('#spaces-total', <?php echo $statsCountSpaces; ?>);
                });
            </script>
        </div>

        <hr>

        <div style="text-align: center;">
            <strong><?php echo Yii::t('DirectoryModule.widgets_views_spaceStats', 'Private spaces'); ?></strong><br><br>

            <input id="spaces-private" class="knob" data-width="120" data-displayprevious="true" data-readOnly="true"
                   data-fgcolor="#7191a8"
                   data-skin="tron"
                   data-thickness=".2" value="0"
                   data-max="<?php echo $statsCountSpaces; ?>"
                   style="width: 75px; position: absolute; margin-top: 53.57142857142857px; margin-left: -112.5px; font-size: 37.5px; border: none; background-image: none; font-family: Arial; font-weight: bold; text-align: center; color: rgb(255, 236, 3); padding: 0px; -webkit-appearance: none; background-position: initial initial; background-repeat: initial initial;">
        </div>
        <script type="text/javascript">
            $(document).ready(function() {
                // animate stats
                animateKnob('#spaces-private', <?php echo $statsCountSpacesHidden; ?>);
            });
        </script>

        <hr>

        <?php if (isset($statsSpaceMostMembers->name)) { ?>
            <div style="text-align: center;">
                <strong><?php echo Yii::t('DirectoryModule.widgets_views_spaceStats', 'Most members'); ?>:
                </strong> <?php echo $statsSpaceMostMembers->name; ?>
            </div>
        <?php } ?>
    </div>
</div>

<script>
    $(function() {
        $(".knob").knob();
    });


    //animate the stats
    function animateKnob(id, value) {

        $(id).animate({value: value}, {
            duration: 1000,
            easing: 'swing',
            step: function() {
                $(id).val(Math.round(this.value)).trigger('change');
            }
        })
    }


</script>