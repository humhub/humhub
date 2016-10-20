<?php


?>
<div class="panel panel-default" id="user-statistics-panel">

    <!-- Display panel menu widget -->
<?php echo \humhub\widgets\PanelMenu::widget(array('id' => 'user-statistics-panel')); ?>

    <div class="panel-heading">
<?php echo Yii::t('DirectoryModule.widgets_views_memberStats', '<strong>Member</strong> stats'); ?>
    </div>
    <div class="panel-body">
        <div class="knob-container" style="text-align: center; opacity: 0;">
            <strong><?php echo Yii::t('DirectoryModule.widgets_views_memberStats', 'Total users'); ?></strong><br><br>

            <input id="user-total" class="knob" data-width="120" data-height="140" data-displayprevious="true" data-readOnly="true"
                   data-fgcolor="<?php echo Yii::$app->settings->get('colorPrimary'); ?>" data-skin="tron"
                   data-thickness=".2" value="<?php echo $statsTotalUsers; ?>"
                   data-max="<?php echo $statsTotalUsers; ?>"
                   style="font-size: 25px !important; margin-top: 44px !important;">
        </div>

        <hr>

        <div class="knob-container" style="text-align: center; opacity: 0;">
            <strong><?php echo Yii::t('DirectoryModule.widgets_views_memberStats', 'Online right now'); ?></strong><br><br>

            <input id="user-online" class="knob" data-width="120"  data-height="140" data-displayprevious="true" data-readOnly="true"
                   data-fgcolor="<?php echo Yii::$app->settings->get('colorInfo'); ?>"
                   data-skin="tron"
                   data-thickness=".2" value="<?php echo $statsUserOnline; ?>"
                   data-max="<?php echo $statsTotalUsers; ?>"
                   style="font-size: 25px !important; margin-top: 44px !important;">
        </div>

        <hr>

        <div style="text-align: center;">
            <strong><?php echo Yii::t('DirectoryModule.widgets_views_memberStats', 'Follows somebody'); ?>:</strong> <?php echo $statsUserFollow; ?>
        </div>


    </div>
</div>

<script>
    $(function () {
        $(".knob").knob();
        $(".knob-container").css("opacity", 1);
    });

</script>
