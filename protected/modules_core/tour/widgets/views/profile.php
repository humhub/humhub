<script type="text/javascript">

    var gotoAdministration = false;

    // Create a new tour
    var profileTour = new Tour({
        storage: false,
        template: '<div class="popover tour"> <div class="arrow"></div> <h3 class="popover-title"></h3> <div class="popover-content"></div> <div class="popover-navigation"> <div class="btn-group"> <button class="btn btn-sm btn-default" data-role="prev"><?php echo Yii::t('TourModule.base', '« Prev'); ?></button> <button class="btn btn-sm btn-default" data-role="next"><?php echo Yii::t('TourModule.base', 'Next »'); ?></button>  </div> <button class="btn btn-sm btn-default" data-role="end"><?php echo Yii::t('TourModule.base', 'End tour'); ?></button> </div> </div>',
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
            title: "<?php echo Yii::t('TourModule.widgets_views_profile', '<strong>Benutzerprofil</strong>'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_profile', "Dies ist dein öffentliches User-Profil, welches für alle registrierten User sichtbar ist."); ?>"
        },
        {
            element: "#user-profile-image",
            title: "<?php echo Yii::t('TourModule.widgets_views_profile', '<strong>Profilbild</strong>'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_profile', 'Ein neues Profilbild kannst hier ganz einfach per Klick oder Drag & Drop hochladen.<br><br>Genauso funktioniert das auch mit dem großen Titelbild.'); ?>",
            placement: "right"
        },
        {
            element: ".controls-account",
            title: "<?php echo Yii::t('TourModule.widgets_views_profile', '<strong>Account</strong> bearbeiten'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_profile', 'Über diesen Button gelangst du zu deinen Profil- und Accounteinstellungen.<br><br>Hier kannst du dein Profil um weitere Informationen ergänzen.'); ?>",
            placement: "left"
        },
        {
            element: ".profile-nav-container",
            title: "<?php echo Yii::t('TourModule.widgets_views_profile', '<strong>Profilmenü</strong>'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_profile', 'Wie auch bei den Spaces, verfügt ein User-Profil über ein Menü, welches mit Hilfe von Modulen erweitert werden kann.<br><br>Welche Module für dein Profil zu Verfügung stehen, siehst du in deinen <strong>Account-Einstellungen</strong> unter <strong>Modules</strong>.'); ?>",
            placement: "right"
        },
        {
            element: "#contentFormBody",
            title: "<?php echo Yii::t('TourModule.widgets_views_profile', '<strong>Profile</strong> stream'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_profile', 'Jedes Profil verfügt ebensfalls über eine eigene Pinnwand. User welche dir Folgen, sehen deine Beiträge dann auf ihrem Dashboard.'); ?>",
            placement: "bottom"
        },
        <?php if (Yii::app()->user->isAdmin() == true) : ?>
        {
            orphan: true,
            backdrop: true,
            title: "<?php echo Yii::t('TourModule.widgets_views_profile', '<strong>Finished</strong>'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_profile', 'Hiermit hast du die Tour für das Benutzerprofil abgeschlossen.<br><br>Weiter mit der Tour:<br>'); ?> <a href='javascript:gotoAdministration = true; tourCompleted();'><?php echo Yii::t("TourModule.widgets_views_profile", "<strong>Administration (Modules)</strong>"); ?></a><br><br>"
        }
        <?php else : ?>
        {
            orphan: true,
            backdrop: true,
            title: "<?php echo Yii::t('TourModule.widgets_views_profile', '<strong>Hurray!</strong> You\'re done.'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_profile', "Hiermit hast du die Tour für das Benutzerprofil abgeschlossen."); ?>"
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
            'url': '<?php echo Yii::app()->createAbsoluteUrl('//tour/tour/TourCompleted', array('section' => 'profile')); ?>',
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