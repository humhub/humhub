<div class="container">
    <div class="row">
        <div class="col-md-2">
            <!-- show space menu widget -->
            <?php $this->widget('application.modules_core.space.widgets.SpaceMenuWidget', array()); ?>
            <!-- show space admin menu widget -->
            <?php $this->widget('application.modules_core.space.widgets.SpaceAdminMenuWidget', array()); ?>
        </div>
        <div class="col-md-7">
            <?php echo $content; ?>
        </div>
        <div class="col-md-3">
            <!-- show sidebar widget for spaces -->

            <?php $this->widget('application.modules_core.space.widgets.SpaceSidebarWidget'); ?>
        </div>
    </div>
</div>
