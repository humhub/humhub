<script type="text/javascript">

    var gotoProfile = false;

    // Create a new tour
    var spacesTour = new Tour({
        storage: false,
        template: '<div class="popover tour"> <div class="arrow"></div> <h3 class="popover-title"></h3> <div class="popover-content"></div> <div class="popover-navigation"> <div class="btn-group"> <button class="btn btn-sm btn-default" data-role="prev"><?php echo Yii::t('TourModule.base', '« Prev'); ?></button> <button class="btn btn-sm btn-default" data-role="next"><?php echo Yii::t('TourModule.base', 'Next »'); ?></button>  </div> <button class="btn btn-sm btn-default" data-role="end"><?php echo Yii::t('TourModule.base', 'End tour'); ?></button> </div> </div>',
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
            title: "<?php echo Yii::t('TourModule.widgets_views_spaces', '<strong>Space</strong>'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_spaces', "In einem Space kannst du mit anderen Usern gemeinsam an Projekten arbeiten, Themen besprechen oder einfach nur Informationen teilen.<br><br>Um die Kommunikation noch produktiver zu gestalten, kann man einen Space mit den verschiedensten Modulen (Tools) erweitern."); ?>"
        },
        {
            element: ".space-nav-container .panel:eq(0)",
            title: "<?php echo Yii::t('TourModule.widgets_views_spaces', '<strong>Space</strong> Menü'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_spaces', 'This is the navigation menu of the space.<br><br>In this particular part you will be able to find the modules which are enabled for it. This might be Polls, Tasks or Notes for example.<br><br>Only a space admin can enable modules for a space.'); ?>",
            placement: "right"
        },
        {
            element: ".space-nav-container .panel:eq(1)",
            title: "<?php echo Yii::t('TourModule.widgets_views_spaces', '<strong>Space</strong> preferences'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_spaces', 'This menu is only visible for space administrators. Here you can manage space settings, members and activate / deactivate modules for this space.'); ?>",
            placement: "right"
        },
        {
            element: "#contentFormBody",
            title: "<?php echo Yii::t('TourModule.widgets_views_spaces', '<strong>Beiträge</strong> schreiben'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_spaces', 'Neue Inhalte erstellst du über dieses Formular.'); ?>",
            placement: "bottom"
        },
        {
            element: ".wall-entry:eq(0)",
            title: "<?php echo Yii::t('TourModule.widgets_views_spaces', '<strong>Beiträge</strong>'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_spaces', 'Erstellte Beiträge erscheinen dann hier im Stream.<br><br>Du und andere User können diese nun liken und kommentieren.'); ?>",
            placement: "bottom"
        },
        {
            element: ".space-info",
            title: "<?php echo Yii::t('TourModule.widgets_views_spaces', '<strong>Space</strong> info'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_spaces', 'This is a short space overview. It should mainly consists of basic information regarding the space. <br /><br />As space administrator you can change the image of the space by clicking on it or via drag & drop.'); ?>",
            placement: "left"
        },
        {
            element: ".panel-activities",
            title: "<?php echo Yii::t('TourModule.widgets_views_spaces', '<strong>Neuste</strong> Aktivitäten'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_spaces', 'Damit du immer auf dem laufenden bist, werden hier die neusten Space-Aktivitäten anderer User angezeigt.'); ?>",
            placement: "left"
        },
        {
            element: "#space-members-panel",
            title: "<?php echo Yii::t('TourModule.widgets_views_spaces', '<strong>Space</strong> members'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_spaces', 'This panel displays a short overview of all space members. <br /><br />Hier kannst du außerdem neue User in den Space einladen, wenn du die benötigten Rechte dafür hast.'); ?>",
            placement: "left"
        },
        {
            orphan: true,
            backdrop: true,
            title: "<?php echo Yii::t('TourModule.widgets_views_spaces', '<strong>Finished</strong>'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_spaces', "Damit hast du die Space-Tour abgeschlossen.<br><br>Weiter gehts mit der Tour: "); ?> <a href='javascript:gotoProfile = true; tourCompleted();'><?php echo Yii::t("TourModule.widgets_views_spaces", "<strong>Benutzerprofil</strong>"); ?></a><br><br>"
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
            'url': '<?php echo Yii::app()->createAbsoluteUrl('//tour/tour/TourCompleted', array('section' => 'spaces')); ?>',
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