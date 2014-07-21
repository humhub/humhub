<script type="text/javascript">

$(document).ready(function () {

        // Create a new tour
        var tour = new Tour({
            //storage: false,
            template: '<div class="popover tour"> <div class="arrow"></div> <h3 class="popover-title"></h3> <div class="popover-content"></div> <div class="popover-navigation"> <div class="btn-group"> <button class="btn btn-sm btn-default" data-role="prev"><?php echo Yii::t('TourModule.widgets_views_index', '« Prev'); ?></button> <button class="btn btn-sm btn-default" data-role="next"><?php echo Yii::t('TourModule.widgets_views_index', 'Next »'); ?></button>  </div> <button class="btn btn-sm btn-default" data-role="end"><?php echo Yii::t('TourModule.widgets_views_index', 'End tour'); ?></button> </div> </div>',
            onEnd: function (tour) {
                tourSeen();
            }
        });

        // Add your steps
        tour.addSteps([
            {
                // step 0
                orphan: true,
                backdrop: true,
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Welcome</strong> to %appName%', array('%appName%' => Yii::app()->name)); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', "This is a little introduction tour, to gives you a quick overview about the most important functions... Let's go!"); ?>"
            },
            {
                // step 1
                orphan: true,
                backdrop: true,
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Dashboard</strong>'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', "You're just on the dashboard.<br><br>This is the entry page, which always provides you a summary of the latest posts and activities."); ?>"
            },
            {
                // step 2
                element: "#space-menu",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Space</strong> selector'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'The heart of %appName% are Spaces.<br><br>A space can be a topic, a group or a project you can communicate about with others.<br><br>Click on the top menu item <strong>%spaceTopMenuEntry%</strong> to open the space selector.', array('%appName%' => Yii::app()->name, '%spaceTopMenuEntry%' => Yii::t('SpaceModule.widgets_views_spaceChooser', 'My spaces'))); ?>",
                reflex: true,
                next: -1,
                placement: "bottom"
            },
            {
                // step 3
                element: "#space-menu-dropdown",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Space</strong> selector'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'Here you will always find a list of all spaces in which you are a member.<br><br>Now, click on the bottom button <strong>%createSpaceButton%</strong> to create your first own space.', array('%createSpaceButton%' => Yii::t('SpaceModule.widgets_views_spaceChooser', 'Create new space'))); ?>",
                reflex: true,
                next: -1,
                onShow: function (tour) {
                    clearInterval(newInterval);
                },
                placement: "right"
            },
            {
                // step 4
                orphan: true,
                backdrop: true,
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Congratulations!</strong>'); ?>",
                prev: -1,
                content: "<?php echo Yii::t('TourModule.widgets_views_index', "You've just created your first own space.<br><br>In the middle you'll usually find the stream, which is still empty now. So let's take a look at the menus and panels on the sides more closely."); ?>"
            },
            {
                // step 5
                element: ".space-nav-container .panel:eq(0)",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Space</strong> menu'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'You can extend a space with any number of modules such as polls, tasks, events or a dropbox integration, for example.<br><br>Depending on which modules are enabled for this space, you can find the corresponding menu entries here.'); ?>",
                placement: "right"
            },
            {
                // step 6
                element: ".space-nav-container .panel:eq(1)",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Space</strong> preferences'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'This menu is only visible for space administrators. You can change settings for the space, manage the members and enable or disable the just mentioned modules here.'); ?>",
                placement: "right"
            },
            {
                // step 7
                element: ".space-info",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Space</strong> info'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'This info panel provides a small orientation for all space users.<br><br>You can change the default space image, simply by drag & drop or by clicking on this placeholder image.'); ?>",
                placement: "left"
            },
            {
                // step 8
                element: "#space-members-panel",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Space</strong> members'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'At this panel you have a short overview about the space members.<br><br>But more important is, that you can invite new users to this space by clicking the invite button.'); ?>",
                placement: "left"
            },
            {
                // step 9
                element: ".dropdown.account",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Account</strong> menu'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'This is the account menu. Within this menu you can manage your account and your public user profile.<br><br>Now click on it to open it.'); ?>",
                reflex: true,
                next: -1,
                placement: "bottom"
            },
            {
                // step 10
                element: ".dropdown.account .dropdown-menu li:eq(0)",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Account</strong> menu'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', "Now, let's take look at your public profile. To do this, click on the menu entry <strong>%profileMenuEntry%</strong>.", array('%profileMenuEntry%' => Yii::t('base', 'My profile'))); ?>",
                next: -1,
                onShow: function (tour) {
                    clearInterval(newInterval);
                },
                placement: "left"
            },
            <?php if (Yii::app()->user->isAdmin() == false) : ?>
            {
                // step 11
                orphan: true,
                backdrop: true,
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Your</strong> Profile'); ?>",
                next: -1,
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'Your profile is your figurehead on the platform.<br><br>You can simply change the profile- and title images by drag & drop and add more information about you by clicking the button <strong>%editAccountButton%</strong>.<br><br>You can start with that right now, because the little introduction ends here. <br><br>Have fun.', array('%editAccountButton%' => Yii::t('UserModule.widgets_views_profileHeader', 'Edit account'))); ?>"
            },
            <?php else : ?>
            {
                // step 12
                orphan: true,
                backdrop: true,
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Your</strong> Profil'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'Your profile is your figurehead on the platform.<br><br>You can simply change the profile- and title images by drag & drop and add more information about you by clicking the button <strong>%editAccountButton%</strong>.', array('%editAccountButton%' => Yii::t('UserModule.widgets_views_profileHeader', 'Edit account'))); ?>"
            },
            <?php endif;  ?>
            {
                // step 13
                element: ".dropdown.account",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Administration</strong>'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'As an administrator, you can make various settings to manage the platform, the spaces and the users.<br><br> Now click again on your account menu to open it.'); ?>",
                reflex: true,
                next: -1,
                placement: "bottom"
            },
            {
                // step 14
                element: ".dropdown.account .dropdown-menu li:eq(3)",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Administration</strong>'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'Click now on <strong>%administrationMenuEntry%</strong> to enter the administration section.', array('%administrationMenuEntry%' => Yii::t('base', 'Administration'))); ?>",
                next: -1,
                reflex: true,
                onShow: function (tour) {
                    clearInterval(newInterval);
                },
                placement: "left"
            },
            {
                // step 15
                orphan: true,
                backdrop: true,
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Administration</strong>'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', "In this area you can make all settings to adjust your platform. <br><br>We won't run through all the points with you, because they are most self-explanatory.<br><br>If you have any questions, don't hesitate to contact us via www.humhub.org."); ?>"
            },
            {
                // step 16
                element: ".list-group-item.modules",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Modules</strong>'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', "But there is one important feature, we still have to show you. It's about how you are managing modules.<br><br>Now click on the menu item <strong>%modulesMenuEntry%</strong> to enter the Module directory.", array('%modulesMenuEntry%' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Modules'))); ?>",
                next: -1,
                reflex: true,
                placement: "right"
            },
            {
                // step 17
                orphan: true,
                backdrop: true,
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Module</strong> directory'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'As mentioned already in the spaces, with modules you can extend the platform to do almost anything you can imagine.<br><br>This section allows you to manage the modules, so other users can use them, for example, in their spaces.'); ?>"
            },
            {
                // step 18
                element: "#moduleTabs li:eq(1)",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Find</strong> modules online'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'With <strong> %browseOnlineButton% </strong> you will get access to the HumHub Marketplace where you can install existing modules written by developers from the HumHub Community.', array('%browseOnlineButton%' => Yii::t('AdminModule.views_module_header', 'Browse online'))); ?>",
                reflex: true,
                placement: "bottom"
            },
            {
                // step 19
                orphan: true,
                backdrop: true,
                title: "<?php echo Yii::t('TourModule.widgets_views_index', "<strong>Hurray!</strong> We're done."); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'This were the most important things, you should know before using the platform.<br><br>We hope that your Social Network project will be successful for you and your users.<br><br>We always looking forward for every suggestion or help to support this project. Please contact us via www.humhub.org.<br><br>Stay tuned.'); ?>"
            }

        ]);

        // Initialize method on the Tour class. Get's everything loaded up and ready to go.
        tour.init();

        // This starts the tour itself
        tour.restart();


        // save current module and controller id's
        var currentModuleId = '<?php echo Yii::app()->controller->module->id; ?>';
        var currentControllerId = '<?php echo Yii::app()->controller->id; ?>';

        if (currentModuleId == 'space') {
            // go to the first step for spaces
            tour.goTo(4);
        }

        if (currentModuleId == 'user') {
            // go to the first step for users (profiles)
            tour.goTo(11);
        }

        if (currentModuleId == 'admin') {

            if (currentControllerId == 'setting') {
                // go to the first step for admin setttings
                tour.goTo(14);
            } else if (currentControllerId == 'module') {
                // go to the first step for admin modules
                tour.goTo(16);
            }
        }


        $('#space-menu').click(function () {
            // show next step after clicking the space menu item
            delayedGoTo(3);
        })

        $('.dropdown.account').click(function () {

            // save current step in variable
            _currentStep = tour.getCurrentStep();

            if (_currentStep == 9) {
                // show next step (profile) after clicking the account menu
                delayedGoTo(10);
            } else if (_currentStep == 12) {
                // show next step (administration) after clicking the account menu
                delayedGoTo(13);
            }

        })

        // global variable for setInterval instances
        var newInterval;

        /**
         * Call single steps delayed (for ajax loaded content)
         * @param nextStep int
         * @param delay int
         */
        function delayedGoTo(nextStep) {

            // create inerval
            newInterval = setInterval(showStep, 10);

            function showStep() {

                // go to specific step
                tour.goTo(nextStep);

            }
        }


        function tourSeen() {
            // load user spaces
            $.ajax({
                'url': '<?php echo Yii::app()->createAbsoluteUrl('tour/tour/seen'); ?>',
                'cache': false,
                'data': jQuery(this).parents("form").serialize()
            });
        }

    }
)
;

</script>