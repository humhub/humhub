<script type="text/javascript">

    var gotoSpace = false;

    function startInterfaceTour() {

        // Create a new tour
        var interfaceTour = new Tour({
            storage: false,
            template: '<div class="popover tour"> <div class="arrow"></div> <h3 class="popover-title"></h3> <div class="popover-content"></div> <div class="popover-navigation"> <div class="btn-group"> <button class="btn btn-sm btn-default" data-role="prev"><?php echo Yii::t('TourModule.base', '« Prev'); ?></button> <button class="btn btn-sm btn-default" data-role="next"><?php echo Yii::t('TourModule.base', 'Next »'); ?></button>  </div> <button class="btn btn-sm btn-default" data-role="end"><?php echo Yii::t('TourModule.base', 'End tour'); ?></button> </div> </div>',
            name: 'interface',
            onEnd: function (tour) {
                tourCompleted();
            }
        });


        // Add tour steps
        interfaceTour.addSteps([
            {
                // step 0
                orphan: true,
                backdrop: true,
                title: "<?php echo Yii::t('TourModule.widgets_views_welcome_interface', '<strong>Dashboard</strong>'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_welcome_interface', "Du befindest dich gerade auf dem Dashboard.<br><br>Hier erhältst du einen für dich relevanten Überblick über alle neue Inhalte und Aktivitäten."); ?>"
            },
            {
                element: "#icon-notifications",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Benachrichtigungen</strong>'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'Um stets den Überblick zu behalten, wirst du über dieses Symbol nur über neue Aktivitäten und Inhalte informiert, welche dich direkt betreffen.'); ?>",
                placement: "bottom"
            },
            {
                element: ".dropdown.account",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Account</strong> Menü'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'Über das Account-Menu kannst du deine Einstellungen ändern und dein öffentliches Benutzer-Profil pflegen.'); ?>",
                placement: "bottom"
            },
            {
                element: "#space-menu",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Space</strong> Auswahl'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'Dies ist das wichtigste und wird wohl dein am meinsten genutztes Menü.<br><br>Hier hast du Zugriff auf alle deine Spaces, in welchen du Mitglied bist und kannst auch neue Spaces erstellen.<br><br>Wie Spaces genau funktionieren, erfährst du in der nächsten Tour:'); ?> <br><br><a href='javascript:gotoSpace = true; tourCompleted();'><?php echo Yii::t("TourModule.widgets_views_index", "<strong>Space-Tour</strong> starten"); ?></a><br><br>",
                placement: "bottom"
            }
        ]);

        // Initialize tour plugin
        interfaceTour.init();

        // start the tour
        interfaceTour.restart();

    }


    /**
     * Set tour as seen
     */
    function tourCompleted() {
        // load user spaces
        $.ajax({
            'url': '<?php echo Yii::app()->createAbsoluteUrl('//tour/tour/TourCompleted', array('section' => 'interface')); ?>',
            'cache': false,
            'data': jQuery(this).parents("form").serialize()
        }).done(function () {
            // cross out welcome tour entry
            $('#interface_entry').addClass('completed');

            if (gotoSpace == true) {

                // redirect to space
                window.location.href="<?php echo Yii::app()->createUrl('//space/space', array('sguid' => $space->guid, 'tour' => 'true')); ?>";
            }
        });
    }



</script>