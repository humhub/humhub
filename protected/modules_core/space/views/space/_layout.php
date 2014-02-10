<div class="container">
    <div class="row">
        <div class="col-md-2">
            <!-- show space menu widget -->
            <?php $this->widget('application.modules_core.space.widgets.SpaceMenuWidget', array()); ?>

            <!-- show space admin menu widget -->
            <?php
            // get current space
            $space = Yii::app()->getController()->getSpace();
            // display admin menu, if user has any administrative rights for this space
            if ($space->canInvite() || $space->isAdmin()) {
                $this->widget('application.modules_core.space.widgets.SpaceAdminMenuWidget', array());
            }
            ?>
        </div>
        <div class="col-md-7">
            <!-- show content -->
            <?php echo $content; ?>
        </div>
        <div class="col-md-3">
            <!-- show modules like activity stream and space member widget -->
            <?php $this->widget('application.modules_core.space.widgets.SpaceSidebarWidget', array(
                'widgets' => array(
                    array('application.modules_core.space.widgets.SpaceInfoWidget', array(), array('sortOrder' => 100)),
                    array('application.modules_core.activity.widgets.ActivityStreamWidget', array('type' => Wall::TYPE_SPACE, 'guid' => $this->getSpace()->guid), array('sortOrder' => 200)),
                    array('application.modules_core.space.widgets.SpaceMemberWidget', array(), array('sortOrder' => 300)),
                )
            )); ?>
        </div>
    </div>

</div>