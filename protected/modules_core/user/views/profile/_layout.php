<div class="container">
    <div class="row">
        <div class="col-md-12">
            <?php $this->widget('application.modules_core.user.widgets.ProfileHeaderWidget', array('user' => $this->getUser())); ?>
        </div>
    </div>
    <div class="row">
        <div class="profile-nav-container col-md-2">
            <?php $this->widget('application.modules_core.user.widgets.ProfileMenuWidget', array('user' => $this->getUser())); ?>
        </div>
        <div class="col-md-7">
            <?php echo $content; ?>
        </div>
        <div class="profile-sidebar-container col-md-3">
            <?php
            $this->widget('application.modules_core.user.widgets.ProfileSidebarWidget', array(
                'widgets' => array(
                    array('application.modules_core.user.widgets.UserTagsWidget', array('user' => $this->getUser()), array('sortOrder' => 10)),
                    array('application.modules_core.user.widgets.UserSpacesWidget', array('user' => $this->getUser())),
                    array('application.modules_core.user.widgets.UserFollowerWidget', array('user' => $this->getUser())),
                )
            ));
            ?>
        </div>
    </div>
</div>
