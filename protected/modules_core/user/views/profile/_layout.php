<div class="container">
    <div class="row">
        <div class="col-md-12">
            <?php
            // import header
            $this->renderPartial('application.modules_core.user.views.profile._header');
            ?>
        </div>

        <div class="col-md-2">
            <?php $this->widget('application.modules_core.user.widgets.ProfileMenuWidget', array()); ?>
        </div>
        <div class="col-md-7">
            <?php echo $content; ?>
        </div>
        <div class="col-md-3">
            <?php
            $this->widget('application.modules_core.user.widgets.ProfileSidebarWidget', array(
                'widgets' => array(
                    //   array('application.modules_core.user.widgets.ProfileActivityWidget', array()),
                    array('application.modules_core.user.widgets.UserSpacesWidget', array()),
                    array('application.modules_core.user.widgets.UserFollowerWidget', array()),
                )
            ));
            ?>
        </div>
    </div>
</div>
