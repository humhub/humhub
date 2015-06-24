<script type="text/javascript">

    var gotoProfile = false;

    // Create a new tour
    var spacesTour = new Tour({
        storage: false,
        template: '<div class="popover tour"> <div class="arrow"></div> <h3 class="popover-title"></h3> <div class="popover-content"></div> <div class="popover-navigation"> <div class="btn-group"> <button class="btn btn-sm btn-default" data-role="prev"><?php echo Yii::t('TourModule.base', '« Prev'); ?></button> <button class="btn btn-sm btn-default" data-role="next"><?php echo Yii::t('TourModule.base', 'Next »'); ?></button>  </div> <button class="btn btn-sm btn-default" data-role="end"><?php echo Yii::t('TourModule.base', 'End guide'); ?></button> </div> </div>',
        name: 'spaces',
        onEnd: function (tour) {
            tourCompleted();
        }
    });


    // Add tour steps
    spacesTour.addSteps([
        {
            orphan: true,
            backdrop: true,
            title: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_spaces', '<strong>Space</strong>')); ?>,
            content: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_spaces', "Once you have joined or created a new space you can work on projects, discuss topics or just share information with other users.<br><br>There are various tools to personalize a space, thereby making the work process more productive.")); ?>
        },
        {
            element: ".space-nav-container .panel:eq(0)",
            title: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_spaces', '<strong>Space</strong> navigation menu')); ?>,
            content: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_spaces', 'This is where you can navigate the space – where you find which modules are active or available for the particular space you are currently in. These could be polls, tasks or notes for example.<br><br>Only the space admin can manage the space\'s modules.')); ?>,
            placement: "right"
        },
        {
            element: ".space-nav-container .panel:eq(1)",
            title: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_spaces', '<strong>Space</strong> preferences')); ?>,
            content: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_spaces', 'This menu is only visible for space admins. Here you can manage your space settings, add/block members and activate/deactivate tools for this space.')); ?>,
            placement: "right"
        },
        {
            element: "#contentFormBody",
            title: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_spaces', '<strong>Writing</strong> posts')); ?>,
            content: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_spaces', 'New posts can be written and posted here.')); ?>,
            placement: "bottom"
        },
        {
            element: ".wall-entry:eq(0)",
            title: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_spaces', '<strong>Posts</strong>')); ?>,
            content: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_spaces', 'Yours, and other users\' posts will appear here.<br><br>These can then be liked or commented on.')); ?>,
            placement: "bottom"
        },
        {
            element: ".space-info",
            title: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_spaces', '<strong>Space</strong> info')); ?>,
            content: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_spaces', 'Give other useres a brief idea what the space is about. You can add the basic information here.<br /><br />The space admin can insert and change the space\'s cover photo either by clicking on it or by drag&drop.')); ?>,
            placement: "left"
        },
        {
            element: ".panel-activities",
            title: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_spaces', '<strong>Most recent</strong> activities')); ?>,
            content: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_spaces', 'To keep you up to date, other users\' most recent activities in this space will be displayed here.')); ?>,
            placement: "left"
        },
        {
            element: "#space-members-panel",
            title: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_spaces', '<strong>Space</strong> members')); ?>,
            content: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_spaces', 'All users who are a member of this space will be displayed here.<br /><br />New members can be added by anyone who has been given access rights by the admin.')); ?>,
            placement: "left"
        },
        {
            orphan: true,
            backdrop: true,
            title: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_spaces', '<strong>Yay! You\'re done.</strong>')); ?>,
            content: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_spaces', "That's it for the space guide.<br><br>To carry on with the user profile guide, click here: ")); ?> + "<a href='javascript:gotoProfile = true; tourCompleted();'><?php echo Yii::t("TourModule.widgets_views_guide_spaces", "<strong>Profile Guide</strong>"); ?></a><br><br>"
        }
    ]);

    // Initialize tour plugin
    spacesTour.init();

    // start the tour
    spacesTour.restart();


    /**
     * Set tour as seen
     */
    function tourCompleted() {
        // load user spaces
        $.ajax({
            'url': '<?php echo Yii::app()->createAbsoluteUrl('//tour/tour/tourCompleted', array('section' => 'spaces')); ?>',
            'cache': false,
            'data': jQuery(this).parents("form").serialize()
        }).done(function () {

            if (gotoProfile == true) {
                // redirect to profile
                window.location.href="<?php echo Yii::app()->createUrl('//user/profile', array('uguid' => Yii::app()->user->guid,'tour' => 'true')); ?>";
            } else {
                // redirect to dashboard
                window.location.href="<?php echo Yii::app()->createUrl('//dashboard/dashboard'); ?>";
            }

        });
    }

</script>