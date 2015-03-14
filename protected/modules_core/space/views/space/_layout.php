<div class="container">
    <div class="row">
        <div class="col-md-12">
            <?php $this->widget('application.modules_core.space.widgets.SpaceHeaderWidget', array('space' => $this->getSpace())); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-2 space-nav-container">
            <?php $this->widget('application.modules_core.space.widgets.SpaceMenuWidget', array('space' => $this->getSpace())); ?>
            <?php $this->widget('application.modules_core.space.widgets.SpaceAdminMenuWidget', array('space' => $this->getSpace())); ?>
            <br/>
        </div>
        <div class="col-md-7 space-stream-container">
            <?php echo $content; ?>
        </div>
        <div class="col-md-3 space-sidebar-container">
            <?php
            $this->widget('application.modules_core.space.widgets.SpaceSidebarWidget', array(
                'widgets' => array(
                    array('application.modules_core.activity.widgets.ActivityStreamWidget', array('type' => Wall::TYPE_SPACE, 'guid' => $this->getSpace()->guid), array('sortOrder' => 100)),
                    array('application.modules_core.space.widgets.SpaceMemberWidget', array('space' => $this->getSpace()), array('sortOrder' => 200)),
                )
            ));
            ?>
        </div>
    </div>
</div>
