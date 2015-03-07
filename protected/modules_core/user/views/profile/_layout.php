<div class="container">
    <div class="row">
        <div class="col-md-12">
            <?php $this->widget('application.modules_core.user.widgets.ProfileHeaderWidget'); ?>
        </div>
    </div>
    <div class="row">
        <div class="profile-nav-container col-md-2">
            <?php if (!Yii::app()->user->isGuest || $this->getUser()->visibility == User::VISIBILITY_ALL) : ?>
                <?php $this->widget('application.modules_core.user.widgets.ProfileMenuWidget', array()); ?>
            <?php endif; ?>
        </div>
        <div class="col-md-7">
            <?php echo $content; ?>
        </div>
        <div class="profile-sidebar-container col-md-3">
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
