<?php
humhub\modules\directory\assets\DirectoryAsset::register($this);
?>

<div class="panel panel-default" id="user-statistics-panel">

    <!-- Display panel menu widget -->
    <?= \humhub\widgets\PanelMenu::widget(['id' => 'user-statistics-panel']); ?>

    <div class="panel-heading">
        <?= Yii::t('DirectoryModule.base', '<strong>Member</strong> stats'); ?>
    </div>

    <div class="panel-body">
        <div class="knob-container" style="text-align: center; opacity: 0;">
            <strong><?= Yii::t('DirectoryModule.base', 'Total users'); ?></strong><br><br>

            <input id="user-total" class="knob" data-width="120" data-height="140" data-displayprevious="true" data-readOnly="true"
                   data-fgcolor="<?= $this->theme->variable('primary'); ?>" data-skin="tron"
                   data-thickness=".2" value="<?= $statsTotalUsers; ?>"
                   data-max="<?= $statsTotalUsers; ?>"
                   style="font-size: 25px !important; margin-top: 44px !important;">
        </div>

        <hr>

        <div class="knob-container" style="text-align: center; opacity: 0;">
            <strong><?= Yii::t('DirectoryModule.base', 'Online right now'); ?></strong><br><br>

            <input id="user-online" class="knob" data-width="120"  data-height="140" data-displayprevious="true" data-readOnly="true"
                   data-fgcolor="<?= $this->theme->variable('primary'); ?>"
                   data-skin="tron"
                   data-thickness=".2" value="<?= $statsUserOnline; ?>"
                   data-max="<?= $statsTotalUsers; ?>"
                   style="font-size: 25px !important; margin-top: 44px !important;">
        </div>

        <hr>

        <div style="text-align: center;">
            <strong><?= Yii::t('DirectoryModule.base', 'Follows somebody'); ?>:</strong> <?php echo $statsUserFollow; ?>
        </div>


    </div>
</div>
