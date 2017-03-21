<?php

use yii\helpers\Html;
?>

<div class="panel panel-default" id="spaces-statistics-panel">

    <!-- Display panel menu widget -->
    <?= \humhub\widgets\PanelMenu::widget(array('id' => 'spaces-statistics-panel')); ?>

    <div class="panel-heading">
        <?= Yii::t('DirectoryModule.widgets_views_spaceStats', '<strong>Space</strong> stats'); ?>
    </div>

    <div class="panel-body">
        <div class="knob-container" style="text-align: center; opacity: 0;">
            <strong><?= Yii::t('DirectoryModule.widgets_views_spaceStats', 'Total spaces'); ?></strong><br><br>

            <input id="spaces-total" class="knob" data-width="120" data-height="140" data-displayprevious="true" data-readOnly="true"
                   data-fgcolor="<?= Yii::$app->settings->get('colorPrimary'); ?>" data-skin="tron"
                   data-thickness=".2" value="<?= $statsCountSpaces; ?>"
                   data-max="<?= $statsCountSpaces; ?>"
                   style="font-size: 25px !important; margin-top: 44px !important;">
        </div>

        <hr>

        <div class="knob-container" style="text-align: center; opacity: 0;">
            <strong><?= Yii::t('DirectoryModule.widgets_views_spaceStats', 'Private spaces'); ?></strong><br><br>

            <input id="spaces-private" class="knob" data-width="120" data-height="140" data-displayprevious="true" data-readOnly="true"
                   data-fgcolor="<?= Yii::$app->settings->get('colorPrimary'); ?>"
                   data-skin="tron"
                   data-thickness=".2" value="<?= $statsCountSpacesHidden; ?>"
                   data-max="<?= $statsCountSpaces; ?>"
                   style="font-size: 25px !important; margin-top: 44px !important;">
        </div>
        <hr>

        <?php if (isset($statsSpaceMostMembers->name)) { ?>
            <div style="text-align: center;">
                <strong><?= Yii::t('DirectoryModule.widgets_views_spaceStats', 'Most members'); ?>:
                </strong> <?= Html::encode($statsSpaceMostMembers->name); ?>
            </div>
        <?php } ?>
    </div>
</div>

<script>
$(function () {
    $(".knob").knob();
    $(".knob-container").css("opacity", 1);
});
</script>
