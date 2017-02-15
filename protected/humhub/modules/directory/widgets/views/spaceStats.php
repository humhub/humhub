<?php

use yii\helpers\Html;

humhub\modules\directory\assets\DirectoryAsset::register($this);
?>

<div class="panel panel-default" id="spaces-statistics-panel">

    <!-- Display panel menu widget -->
    <?= \humhub\widgets\PanelMenu::widget(array('id' => 'spaces-statistics-panel')); ?>

    <div class="panel-heading">
        <?php echo Yii::t('DirectoryModule.base', '<strong>Space</strong> stats'); ?>
    </div>

    <div class="panel-body">
        <div class="knob-container" style="text-align: center; opacity: 0;">
            <strong><?php echo Yii::t('DirectoryModule.base', 'Total spaces'); ?></strong><br><br>

            <input id="spaces-total" class="knob" data-width="120" data-height="140" data-displayprevious="true" data-readOnly="true"
                   data-fgcolor="<?= $this->theme->variable('primary'); ?>" data-skin="tron"
                   data-thickness=".2" value="<?php echo $statsCountSpaces; ?>"
                   data-max="<?php echo $statsCountSpaces; ?>"
                   style="font-size: 25px !important; margin-top: 44px !important;">
        </div>

        <hr>

        <div class="knob-container" style="text-align: center; opacity: 0;">
            <strong><?php echo Yii::t('DirectoryModule.base', 'Private spaces'); ?></strong><br><br>

            <input id="spaces-private" class="knob" data-width="120" data-height="140" data-displayprevious="true" data-readOnly="true"
                   data-fgcolor="<?= $this->theme->variable('primary'); ?>"
                   data-skin="tron"
                   data-thickness=".2" value="<?php echo $statsCountSpacesHidden; ?>"
                   data-max="<?php echo $statsCountSpaces; ?>"
                   style="font-size: 25px !important; margin-top: 44px !important;">
        </div>
        <hr>

        <?php if (isset($statsSpaceMostMembers->name)) { ?>
            <div style="text-align: center;">
                <strong><?php echo Yii::t('DirectoryModule.base', 'Most members'); ?>:
                </strong> <?php echo Html::encode($statsSpaceMostMembers->name); ?>
            </div>
        <?php } ?>
    </div>
</div>
