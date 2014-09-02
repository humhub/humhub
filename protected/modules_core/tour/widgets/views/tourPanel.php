<div class="panel panel-default panel-tour" id="getting-started-panel">
    <!-- Display panel menu widget -->
    <?php $this->widget('application.widgets.PanelMenuWidget', array('id' => 'getting-started-panel')); ?>
    <div class="panel-heading">
        <?php echo Yii::t('TourModule.widgets_views_tourPanel', '<strong>Getting</strong> Started'); ?>


    </div>
    <div class="panel-body">
        <p>
            <?php echo Yii::t('TourModule.widgets_views_tourPanel', 'Get to know your way around the site\'s most important features with the following guides:'); ?>
        </p>

        <?php
        $interface = Yii::app()->user->getModel()->getSetting("interface", "tour");
        $spaces = Yii::app()->user->getModel()->getSetting("spaces", "tour");
        $profile = Yii::app()->user->getModel()->getSetting("profile", "tour");
        $administration = Yii::app()->user->getModel()->getSetting("administration", "tour");
        ?>

        <ul class="tour-list">
            <li id="interface_entry" class="<?php if ($interface == 1) : ?>completed<?php endif; ?>"><a href="javascript:startInterfaceTour();"><i class="fa fa-play-circle-o"></i> <strong>Guide: </strong> Overview</a></li>
            <li class="<?php if ($spaces == 1) : ?>completed<?php endif; ?>"><a id="interface-tour-link" href="<?php echo Yii::app()->createUrl('//space/space', array('sguid' => $space->guid, 'tour' => 'true')); ?>"><i class="fa fa-play-circle-o"></i> <strong>Guide: </strong> Spaces</a></li>
            <li class="<?php if ($profile == 1) : ?>completed<?php endif; ?>"><a href="<?php echo Yii::app()->createUrl('//user/profile', array('uguid' => Yii::app()->user->guid,'tour' => 'true')); ?>"><i class="fa fa-play-circle-o"></i> <strong>Guide: </strong> User profile</a></li>
            <?php if (Yii::app()->user->isAdmin() == true) : ?>
            <li class="<?php if ($administration == 1) : ?>completed<?php endif; ?>"><a href="<?php echo Yii::app()->createUrl('//admin/module/listOnline', array('tour' => 'true')); ?>"><i class="fa fa-play-circle-o"></i> <strong>Guide: </strong> Administration (Modules)</a></li>
            <?php endif;?>
        </ul>
    </div>
</div>
