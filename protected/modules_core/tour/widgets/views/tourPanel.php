<div class="panel panel-default panel-tour">
    <div class="panel-heading">
        <?php echo Yii::t('TourModule.widgets_views_tourPanel', '<strong>Getting</strong> Started'); ?>

        <!-- load modal confirm widget -->
        <?php $this->widget('application.widgets.ModalConfirmWidget', array(
            'uniqueID' => 'hide-panel-button',
            'linkOutput' => 'button',
            'title' => '<strong>Hide</strong> tour panel',
            'message' => 'This action will remove the tour panel from your dashboard. You can reactivate it under<br>Account settings <i class="fa fa-caret-right"></i> Settings.',
            'buttonTrue' => 'Ok',
            'buttonFalse' => 'Cancel',
            'class' => 'pull-right btn btn-sm btn-danger',
            'linkContent' => Yii::t('TourModule.widgets_views_tourPanel', 'Hide Panel'),
            'linkHref' => $this->createUrl("//tour/tour/hidePanel", array("ajax" => 1)),
            'confirmJS' => '$(".panel-tour").slideToggle("slow")'
        )); ?>

    </div>
    <div class="panel-body">
        <p>
            Text für die folgenden Touren
        </p>

        <?php
        $interface = Yii::app()->user->getModel()->getSetting("interface", "tour");
        $spaces = Yii::app()->user->getModel()->getSetting("spaces", "tour");
        $profile = Yii::app()->user->getModel()->getSetting("profile", "tour");
        $administration = Yii::app()->user->getModel()->getSetting("administration", "tour");
        ?>

        <ul class="tour-list">
            <li id="interface_entry" class="<?php if ($interface == 1) : ?>completed<?php endif; ?>"><a href="javascript:startInterfaceTour();"><i class="fa fa-play-circle-o"></i> <strong>Step 1: </strong> Einführung</a></li>
            <li class="<?php if ($spaces == 1) : ?>completed<?php endif; ?>"><a id="interface-tour-link" href="<?php echo Yii::app()->createUrl('//space/space', array('sguid' => $space->guid, 'tour' => 'true')); ?>"><i class="fa fa-play-circle-o"></i> <strong>Step 2: </strong> Spaces</a></li>
            <li class="<?php if ($profile == 1) : ?>completed<?php endif; ?>"><a href="<?php echo Yii::app()->createUrl('//user/profile', array('uguid' => Yii::app()->user->guid,'tour' => 'true')); ?>"><i class="fa fa-play-circle-o"></i> <strong>Step 3: </strong> User profile</a></li>
            <?php if (Yii::app()->user->isAdmin() == true) : ?>
            <li class="<?php if ($administration == 1) : ?>completed<?php endif; ?>"><a href="<?php echo Yii::app()->createUrl('//admin/module/listOnline', array('tour' => 'true')); ?>"><i class="fa fa-play-circle-o"></i> <strong>Step 4: </strong> Administration / Modules</a></li>
            <?php endif;?>
        </ul>
    </div>
</div>
