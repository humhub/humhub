<script type="text/javascript">

    var gotoAdministration = false;

    // Create a new tour
    var profileTour = new Tour({
        storage: false,
        template: '<div class="popover tour"> <div class="arrow"></div> <h3 class="popover-title"></h3> <div class="popover-content"></div> <div class="popover-navigation"> <div class="btn-group"> <button class="btn btn-sm btn-default" data-role="prev"><?php echo Yii::t('TourModule.base', '« Prev'); ?></button> <button class="btn btn-sm btn-default" data-role="next"><?php echo Yii::t('TourModule.base', 'Next »'); ?></button>  </div> <button class="btn btn-sm btn-default" data-role="end"><?php echo Yii::t('TourModule.base', 'End guide'); ?></button> </div> </div>',
        name: 'profile',
        onEnd: function (tour) {
            tourCompleted();
        }
    });


    // Add tour steps
    profileTour.addSteps([
        {
            orphan: true,
            backdrop: true,
            title: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_profile', '<strong>User profile</strong>')); ?>,
            content: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_profile', "This is your public user profile, which can be seen by any registered user.")); ?>
        },
        {
            element: "#user-profile-image",
            title: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_profile', '<strong>Profile</strong> photo')); ?>,
            content: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_profile', 'Upload a new profile photo by simply clicking here or by drag&drop. Do just the same for updating your cover photo.')); ?>,
            placement: "right"
        },
        {
            element: ".controls-account",
            title: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_profile', '<strong>Edit</strong> account')); ?>,
            content: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_profile', 'Click on this button to update your profile and account settings. You can also add more information to your profile.')); ?>,
            placement: "left"
        },
        {
            element: ".profile-nav-container",
            title: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_profile', '<strong>Profile</strong> menu')); ?>,
            content: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_profile', 'Just like in the space, the user profile can be personalized with various modules.<br><br>You can see which modules are available for your profile by looking them in “Modules” in the account settings menu.')); ?>,
            placement: "right"
        },
        {
            element: "#contentFormBody",
            title: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_profile', '<strong>Profile</strong> stream')); ?>,
            content: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_profile', 'Each profile has its own pin board. Your posts will also appear on the dashboards of those users who are following you.')); ?>,
            placement: "bottom"
        },
        <?php if (Yii::app()->user->isAdmin() == true) : ?>
        {
            orphan: true,
            backdrop: true,
            title: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_profile', '<strong>Hurray!</strong> You\'re done!')); ?>,
            content: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_profile', 'You\'ve completed the user profile guide!<br><br>To carry on with the administration guide, click here:<br /><br />')); ?> + "<a href='javascript:gotoAdministration = true; tourCompleted();'><?php echo Yii::t("TourModule.widgets_views_guide_profile", "<strong>Administration (Modules)</strong>"); ?></a><br><br>"
        }
        <?php else : ?>
        {
            orphan: true,
            backdrop: true,
            title: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_profile', '<strong>Hurray!</strong> The End.')); ?>,
            content: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_profile', "You've completed the user profile guide!")); ?>
        }
        <?php endif; ?>

    ]);

    // Initialize tour plugin
    profileTour.init();

    // start the tour
    profileTour.restart();


    /**
     * Set tour as seen
     */
    function tourCompleted() {
        // load user spaces
        $.ajax({
            'url': '<?php echo Yii::app()->createAbsoluteUrl('//tour/tour/tourCompleted', array('section' => 'profile')); ?>',
            'cache': false,
            'data': jQuery(this).parents("form").serialize()
        }).done(function () {
            // redirect to dashboard
            window.location.href="<?php echo Yii::app()->createUrl('//dashboard/dashboard'); ?>";

            if (gotoAdministration == true) {
                // redirect to administration
                window.location.href="<?php echo Yii::app()->createUrl('//admin/module/listOnline', array('tour' => 'true')); ?>";
            } else {
                // redirect to dashboard
                window.location.href="<?php echo Yii::app()->createUrl('//dashboard/dashboard'); ?>";
            }
        });
    }

</script>