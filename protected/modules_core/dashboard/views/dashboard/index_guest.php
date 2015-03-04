<div class="container">
    <div class="row">
        <div class="col-md-8">
            <?php
            $this->widget('application.modules_core.wall.widgets.WallStreamWidget', array(
                'type' => Wall::TYPE_DASHBOARD
            ));
            ?>
        </div>
        <div class="col-md-4">
            <?php
            $this->widget('application.modules_core.dashboard.widgets.DashboardSidebarWidget', array(
                'widgets' => array(
                    array('application.modules_core.directory.widgets.NewMembersWidget', array('showMoreButton'=>true), array('sortOrder' => 10)),
                    array('application.modules_core.directory.widgets.NewSpacesWidget', array('showMoreButton'=>true), array('sortOrder' => 10)),
                )
            ));
            ?>
        </div>
    </div>
</div>
