<?php
/**
 * Used by MemberStatisticsWidget to display statistics in the sidebar.
 *
 * @package humhub.modules_core.directory.views
 * @since 0.5
 */
?>

<div class="panel panel-default members">
    <div class="panel-heading">
        <?php echo Yii::t('DirectoryModule.base', 'New people'); ?>
    </div>
    <div class="panel-body">
        <?php foreach ($newUsers as $user) : ?>
            <a href="<?php echo $user->getProfileUrl(); ?>">
                <img src="<?php echo $user->getProfileImage()->getUrl(); ?>" class="img-rounded tt img_margin"
                     height="40" width="40" alt="40x40" data-src="holder.js/40x40" style="width: 40px; height: 40px;"
                     data-toggle="tooltip" data-placement="top" title=""
                     data-original-title="<strong><?php echo $user->displayName; ?></strong><br><?php echo $user->title; ?>">
            </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <?php echo Yii::t('DirectoryModule.base', 'Statistics'); ?>
    </div>
    <div class="panel-body">
        <div style="text-align: center;">
            <strong><?php echo Yii::t('DirectoryModule.base', 'Total users'); ?></strong><br><br>

            <input id="user-total" class="knob" data-width="120" data-displayprevious="true" data-readOnly="true"
                   data-fgcolor="#34c7ff" data-skin="tron"
                   data-thickness=".2" value="0"
                   style="width: 75px; position: absolute; margin-top: 53.57142857142857px; margin-left: -112.5px; font-size: 37.5px; border: none; background-image: none; font-family: Arial; font-weight: bold; text-align: center; color: rgb(255, 236, 3); padding: 0px; -webkit-appearance: none; background-position: initial initial; background-repeat: initial initial;">
            <script type="text/javascript">
                $(document).ready(function () {
                    // animate stats
                    animateKnob('#user-total', <?php echo $statsTotalUsers; ?>);
                });
            </script>
        </div>

        <hr>

        <div style="text-align: center;">
            <strong><?php echo Yii::t('DirectoryModule.base', 'Online right now'); ?></strong><br><br>

            <input id="user-online" class="knob" data-width="120" data-displayprevious="true" data-readOnly="true"
                   data-fgcolor="#85dc0a"
                   data-skin="tron"
                   data-thickness=".2" value="0"
                   data-max="<?php echo $statsTotalUsers; ?>"
                   style="width: 75px; position: absolute; margin-top: 53.57142857142857px; margin-left: -112.5px; font-size: 37.5px; border: none; background-image: none; font-family: Arial; font-weight: bold; text-align: center; color: rgb(255, 236, 3); padding: 0px; -webkit-appearance: none; background-position: initial initial; background-repeat: initial initial;">
        </div>
        <script type="text/javascript">
            $(document).ready(function () {
                // animate stats
                animateKnob('#user-online', <?php echo $statsUserOnline; ?>);
            });
        </script>

        <hr>

        <div style="text-align: center;">
            <strong><?php echo Yii::t('DirectoryModule.base', 'Follows somebody'); ?>:</strong> <?php echo $statsUserFollow; ?>
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
