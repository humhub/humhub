<div class="panel panel-default panel-tour" id="getting-started-panel">
    <?php
    // Temporary workaround till panel widget rewrite in 0.10 verion
    $removeOptionHtml = "<li>" . $this->widget('application.widgets.ModalConfirmWidget', array(
                'uniqueID' => 'hide-panel-button',
                'title' => '<strong>Remove</strong> tour panel',
                'message' => 'This action will remove the tour panel from your dashboard. You can reactivate it at<br>Account settings <i class="fa fa-caret-right"></i> Settings.',
                'buttonTrue' => 'Ok',
                'buttonFalse' => 'Cancel',
                'linkContent' => '<i class="fa fa-eye-slash"></i> ' . Yii::t('TourModule.widgets_views_tourPanel', ' Remove panel'),
                'linkHref' => $this->createUrl("//tour/tour/hidePanel", array("ajax" => 1)),
                'confirmJS' => '$(".panel-tour").slideToggle("slow")'
                    ), true) . "</li>";
    ?>

    <!-- Display panel menu widget -->
    <?php $this->widget('application.widgets.PanelMenuWidget', array('id' => 'getting-started-panel', 'extraMenus' => $removeOptionHtml)); ?>

    <div class="panel-heading">
        <?php echo Yii::t('TourModule.widgets_views_tourPanel', '<strong>Getting</strong> Started'); ?>
    </div>
    <div class="panel-body">
        <p>
            <?php echo Yii::t('TourModule.widgets_views_tourPanel', 'Get to know your way around the site\'s most important features with the following guides:'); ?>
        </p>

        <?php
        // Get states of current guides (to mark them as done)
        $interface = Yii::app()->user->getModel()->getSetting("interface", "tour");
        $spaces = Yii::app()->user->getModel()->getSetting("spaces", "tour");
        $profile = Yii::app()->user->getModel()->getSetting("profile", "tour");
        $administration = Yii::app()->user->getModel()->getSetting("administration", "tour");
        ?>

        <ul class="tour-list">
            <li id="interface_entry" class="<?php if ($interface == 1) : ?>completed<?php endif; ?>"><a href="<?php echo Yii::app()->createUrl('//dashboard/dashboard', array('tour' => true)); ?>"><i class="fa fa-play-circle-o"></i><?php echo Yii::t('TourModule.widgets_views_tourPanel', '<strong>Guide:</strong> Overview'); ?></a></li>
            <li class="<?php if ($spaces == 1) : ?>completed<?php endif; ?>"><a id="interface-tour-link" href="<?php echo Yii::app()->createUrl('//tour/tour/startSpaceTour'); ?>"><i class="fa fa-play-circle-o"></i><?php echo Yii::t('TourModule.widgets_views_tourPanel', '<strong>Guide:</strong> Spaces'); ?></a></li>
            <li class="<?php if ($profile == 1) : ?>completed<?php endif; ?>"><a href="<?php echo Yii::app()->createUrl('//user/profile', array('uguid' => Yii::app()->user->guid, 'tour' => 'true')); ?>"><i class="fa fa-play-circle-o"></i><?php echo Yii::t('TourModule.widgets_views_tourPanel', '<strong>Guide:</strong> User profile'); ?></a></li>
            <?php if (Yii::app()->user->isAdmin() == true) : ?>
                <li class="<?php if ($administration == 1) : ?>completed<?php endif; ?>"><a href="<?php echo Yii::app()->createUrl('//admin/module/listOnline', array('tour' => 'true')); ?>"><i class="fa fa-play-circle-o"></i><?php echo Yii::t('TourModule.widgets_views_tourPanel', '<strong>Guide:</strong> Administration (Modules)'); ?></a></li>
                    <?php endif; ?>
        </ul>
    </div>
</div>
