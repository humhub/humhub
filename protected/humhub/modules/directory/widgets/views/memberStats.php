<?php
?>
<div class="panel panel-default" id="user-statistics-panel">

    <!-- Display panel menu widget -->
    <?= \humhub\widgets\PanelMenu::widget(array('id' => 'user-statistics-panel')); ?>

    <div class="panel-heading">
        <?= Yii::t('DirectoryModule.widgets_views_memberStats', '<strong>Member</strong> stats'); ?>
    </div>
    <div class="panel-body">
        <div class="knob-container" style="text-align: center; opacity: 0;">
            <strong><?= Yii::t('DirectoryModule.widgets_views_memberStats', 'Total users'); ?></strong><br><br>

            <input id="user-total" class="knob" data-width="120" data-height="140" data-displayprevious="true" data-readOnly="true"
                   data-fgcolor="<?= Yii::$app->settings->get('colorPrimary'); ?>" data-skin="tron"
                   data-thickness=".2" value="<?= $statsTotalUsers; ?>"
                   data-max="<?= $statsTotalUsers; ?>"
                   style="font-size: 25px !important; margin-top: 44px !important;">
        </div>

        <hr>

        <div class="knob-container" style="text-align: center; opacity: 0;">
            <strong><?= Yii::t('DirectoryModule.widgets_views_memberStats', 'Online right now'); ?></strong><br><br>

            <input id="user-online" class="knob" data-width="120"  data-height="140" data-displayprevious="true" data-readOnly="true"
                   data-fgcolor="<?= Yii::$app->settings->get('colorInfo'); ?>"
                   data-skin="tron"
                   data-thickness=".2" value="<?= $statsUserOnline; ?>"
                   data-max="<?= $statsTotalUsers; ?>"
                   style="font-size: 25px !important; margin-top: 44px !important;">
        </div>

        <hr>

        <div style="text-align: center;">
            <strong><?= Yii::t('DirectoryModule.widgets_views_memberStats', 'Follows somebody'); ?>:</strong> <?= $statsUserFollow; ?>
        </div>


    </div>
</div>

<script>
$(function () {
    $(".knob").knob();
    $(".knob-container").css("opacity", 1);
});

</script>
