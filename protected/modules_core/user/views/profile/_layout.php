<div class="container">

    <div class="row">

        <div class="col-md-9">
            <?php
            // import header
            $this->renderPartial('application.modules_core.user.views.profile.header');
            ?>

            <div class="row">
                <div class="col-md-3">
                    <?php $this->widget('application.modules_core.user.widgets.ProfileMenuWidget', array()); ?>
                </div>
                <div class="col-md-9">
                    <?php echo $content; ?>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <?php
            $this->widget('application.modules_core.user.widgets.ProfileSidebarWidget', array(
                'widgets' => array(
                    //   array('application.modules_core.user.widgets.ProfileActivityWidget', array()),
                    array('application.modules_core.user.widgets.UserTagsWidget', array(), array('sortOrder' => 10)),
                    array('application.modules_core.user.widgets.UserSpacesWidget', array()),
                    array('application.modules_core.user.widgets.UserFollowerWidget', array()),
                )
            ));
            ?>

        </div>

    </div>


</div>
