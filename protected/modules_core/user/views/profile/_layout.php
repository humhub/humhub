<div class="container profile-layout-container">
    <div class="row">
        <div class="col-md-12">
            <?php $this->widget('application.modules_core.user.widgets.ProfileHeaderWidget', array('user' => $this->getUser())); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-2 layout-nav-container">
            <?php $this->widget('application.modules_core.user.widgets.ProfileMenuWidget', array('user' => $this->getUser())); ?>
        </div>

        <?php if (isset($this->hideSidebar) && $this->hideSidebar) : ?>
            <div class="col-md-10 layout-content-container">
                <?php echo $content; ?>
            </div>
        <?php else: ?>
            <div class="col-md-7 layout-content-container">
                <?php echo $content; ?>
            </div>
            <div class="col-md-3 layout-sidebar-container">
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
        <?php endif; ?>
    </div>
</div>
