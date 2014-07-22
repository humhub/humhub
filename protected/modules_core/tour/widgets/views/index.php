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
                content: "<?php echo Yii::t('TourModule.widgets_views_index', "This is a brief introduction to give you an overview about the most important functions... Let's go!"); ?>"
            },
            {
                // step 1
                orphan: true,
                backdrop: true,
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Dashboard</strong>'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', "You're now on the dashboard.<br><br>This is your starting page, which provides you with a summary of the latest content and activities."); ?>"
            },
            {
                // step 2
                element: "#space-menu",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Space</strong> selector'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'The heart of %appName% are Spaces.<br><br>A space can be a topic, a group or a project, within you can communicate with others.<br><br>Click on <strong>\"%spaceTopMenuEntry%\"</strong> on the top left side to select a space.', array('%appName%' => Yii::app()->name, '%spaceTopMenuEntry%' => Yii::t('SpaceModule.widgets_views_spaceChooser', 'My spaces'))); ?>",
                reflex: true,
                next: -1,
                placement: "bottom"
            },
            {
                // step 3
                element: "#space-menu-dropdown",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>My Spaces</strong>'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'Here you will find a list of spaces, which you are a member of.<br><br>To create your first own space, click on <strong>\"%createSpaceButton%\"</strong>.', array('%createSpaceButton%' => Yii::t('SpaceModule.widgets_views_spaceChooser', 'Create new space'))); ?>",
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
                content: "<?php echo Yii::t('TourModule.widgets_views_index', "You've just created a new space.<br><br>In the center of it, future members content will be shown."); ?>"
            },
            {
                // step 5
                element: ".space-nav-container .panel:eq(0)",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Space</strong> menu'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'This is the navigation of your space. In this particular part you will be able to find the modules which are enabled for it. This might be Polls, Tasks or Notes for example.<br /> <br />You\'ll learn more about modules in the later stages of this tutorial.'); ?>",
                placement: "right"
            },
            {
                // step 6
                element: ".space-nav-container .panel:eq(1)",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Space</strong> preferences'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'This menu is only visible for space administrators. You can manage members, modules or settings in this section.<br /><br />Note: Module availability may vary depending on System Administrators decision.'); ?>",
                placement: "right"
            },
            {
                // step 7
                element: ".space-info",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Space</strong> info'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'This is a short space overview. It should mainly consists of basic information regarding the space. <br /><br />You may change the image of the space by clicking on it or via drag & drop.'); ?>",
                placement: "left"
            },
            {
                // step 8
                element: "#space-members-panel",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Space</strong> members'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'This panel displays a short overview of all space members. <br /><br />Furthermore it provides the ability to invite new members to this space.'); ?>",
                placement: "left"
            },
            {
                // step 9
                element: ".dropdown.account",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Account</strong> menu'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', ' Within this menu you are able to manage your personal account settings.<br><br>Click on it to continue.'); ?>",
                reflex: true,
                next: -1,
                placement: "bottom"
            },
            {
                // step 10
                element: ".dropdown.account .dropdown-menu li:eq(0)",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Account</strong> menu'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', "To view your personal profile click on <strong>\\\"%profileMenuEntry%\\\"</strong>.", array('%profileMenuEntry%' => Yii::t('base', 'My profile'))); ?>",
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
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'To manage your profile, which is visible by everybody click on  <strong>\"%editAccountButton%\"</strong>.<br><br>You can simply change the profile and title images via drag & drop.<br><br>Our little introduction ends here, so you can start right now with that. <br><br>Have fun.', array('%editAccountButton%' => Yii::t('UserModule.widgets_views_profileHeader', 'Edit account'))); ?>"
            },
            <?php else : ?>
            {
                // step 12
                orphan: true,
                backdrop: true,
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Your</strong> Profile'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'To manage your profile, which ist visible by everybody click on  <strong>\"%editAccountButton%\"</strong>.<br><br>You can simply change the profile and title images via drag & drop.<br><br>', array('%editAccountButton%' => Yii::t('UserModule.widgets_views_profileHeader', 'Edit account'))); ?>"
            },
            <?php endif;  ?>
            {
                // step 13
                element: ".dropdown.account",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Administration</strong>'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'As an administrator, you are able to decide about various settings of the platform.<br><br> Open your account menu to proceed.'); ?>",
                reflex: true,
                next: -1,
                placement: "bottom"
            },
            {
                // step 14
                element: ".dropdown.account .dropdown-menu li:eq(3)",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Administration</strong>'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'Now click on <strong>\"%administrationMenuEntry%\"</strong> to enter the administration section.', array('%administrationMenuEntry%' => Yii::t('base', 'Administration'))); ?>",
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
                content: "<?php echo Yii::t('TourModule.widgets_views_index', "In this area you can define all settings to adjust your platform. <br><br>We won't run through all availible options, because most of them are self-explanatory.<br><br>For any further questions, don't hesitate to contact us on our official website www.humhub.org."); ?>"
            },
            {
                // step 16
                element: ".list-group-item.modules",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Modules</strong>'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', "But before we complete this tutorial entirely, there is one important feature we still have to show you. It's about managing modules.<br><br>Now click on the menu item <strong>\\\"%modulesMenuEntry%\\\"</strong> to enter the Module directory.", array('%modulesMenuEntry%' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Modules'))); ?>",
                next: -1,
                reflex: true,
                placement: "right"
            },
            {
                // step 17
                orphan: true,
                backdrop: true,
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Module</strong> directory'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'As mentioned before, you are freely able to extend the whole platforms functionality with modules.<br><br>This section allows you to manage the modules, so other users can use them, for example, in their spaces.'); ?>"
            },
            {
                // step 18
                element: "#moduleTabs li:eq(1)",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Find</strong> modules online'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'With <strong>\"%browseOnlineButton%\"</strong> you will get access to the HumHub Marketplace, where you can install existing modules written by developers from all over the world.', array('%browseOnlineButton%' => Yii::t('AdminModule.views_module_header', 'Browse online'))); ?>",
                reflex: true,
                placement: "bottom"
            },
            {
                // step 19
                orphan: true,
                backdrop: true,
                title: "<?php echo Yii::t('TourModule.widgets_views_index', "<strong>Hurray!</strong> We're done."); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'This have been the most important things you should know before using the platform.<br><br>We hope your and your users experience will meet your expectations.<br><br>We are always looking forward for every suggestion or any kind of help to support the project. Feel free to contact us via www.humhub.org.<br><br>Stay tuned. :)'); ?>"
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