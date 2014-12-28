<div class="container">

    <div class="row">
        <div class="col-md-9">
            <?php $this->widget('application.modules_core.user.widgets.ProfileHeaderWidget', array('privacySettings' => $this->privacySettings));?>
            <div class="row">
                <div class="profile-nav-container col-md-3">
                    <?php $this->widget('application.modules_core.user.widgets.ProfileMenuWidget', array()); ?>
                </div>
                <div class="col-md-9">
                    <?php echo $content; ?>
                </div>
            </div>
        </div>

        <div class="profile-sidebar-container col-md-3">
            <?php
            $displayWidgets = array(
            	//   array('application.modules_core.user.widgets.ProfileActivityWidget', array()),
            	array('application.modules_core.user.widgets.UserTagsWidget', array(), array('sortOrder' => 10))
			);
			if ($this->privacySettings['displaySpaceInfo'])
				$displayWidgets[] = array('application.modules_core.user.widgets.UserSpacesWidget', array());
			if ($this->privacySettings['displayFollowingInfo'])
				$displayWidgets[] = array('application.modules_core.user.widgets.UserFollowerWidget', array());
            	
            $this->widget('application.modules_core.user.widgets.ProfileSidebarWidget', array(
                'widgets' => $displayWidgets
            ));
            ?>

        </div>

    </div>


</div>
