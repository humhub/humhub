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
            title: "<?php echo Yii::t('TourModule.widgets_views_profile', '<strong>User</strong> profile'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_profile', "Dies ist dein öffentliches User-Profil, welches für alle registrierten User sichtbar ist."); ?>"
        },
        {
            element: "#user-profile-image",
            title: "<?php echo Yii::t('TourModule.widgets_views_profile', '<strong>Profile</strong> images'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_profile', 'Ein neues Profilbild kannst einfach per Drag & Drop auf das jetzige hochladen.<br><br>Genauso funktioniert das auch mit dem großen Titelbild.'); ?>",
            placement: "right"
        },
        {
            element: ".controls-account",
            title: "<?php echo Yii::t('TourModule.widgets_views_profile', '<strong>Edit</strong> account'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_profile', 'Über diesen Button gelangst du zu deinen Profil- und Accounteinstellungen.<br><br>Hier kannst du u. a. dein Profil um weitere Informationen über dich einstellen.'); ?>",
            placement: "left"
        },
        {
            element: ".profile-nav-container",
            title: "<?php echo Yii::t('TourModule.widgets_views_profile', '<strong>Profile</strong> menu'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_profile', 'Wie auch bei den Spaces, verfügt ein User-Profil über ein Menü, welches mit Hilfe von Modulen erweitert werden kann.<br><br>Welche Module für dein Profil zu Verfügung stehen, siehst du in deinen <strong>Account-Einstellungen</strong> unter <strong>Modules</strong>.'); ?>",
            placement: "right"
        },
        {
            element: "#contentFormBody",
            title: "<?php echo Yii::t('TourModule.widgets_views_profile', '<strong>Profile</strong> stream'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_profile', 'Jedes Profil verfügt ebensfall über eine eigene Pinnwand. User welcher dir Folgen, sehen diese Beiträge auch auf ihrem Dashboard.'); ?>",
            placement: "bottom"
        },
        {
            element: ".profile-sidebar-container",
            title: "<?php echo Yii::t('TourModule.widgets_views_profile', '<strong>Profile</strong> Panels'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_profile', 'Je nach eingegebenen Informationen und User-Aktivität auf der Platform werden hier weitere Panels mit Informationen zum User angezeigt'); ?>",
            placement: "left"
        },
        <?php if (Yii::app()->user->isAdmin() == true) : ?>
        {
            orphan: true,
            backdrop: true,
            title: "<?php echo Yii::t('TourModule.widgets_views_profile', '<strong>Finished</strong>'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_profile', 'Hiermit hast du auch den Step für das User-Profil hinter dich gebracht.<br><br>Zu guter Letzt folgt:<br>'); ?> <a href='javascript:gotoAdministration = true; tourCompleted();'><?php echo Yii::t("TourModule.widgets_views_profile", "<strong>Step 4: </strong> Administration / Modules"); ?></a><br><br>"
        }
        <?php else : ?>
        {
            orphan: true,
            backdrop: true,
            title: "<?php echo Yii::t('TourModule.widgets_views_profile', '<strong>Hurray!</strong> You\'re done.'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_profile', "Hiermit hast du die wichtigsten Funktionen der Plattform kennengelernt.<br><br>Wir wünschen dir viel Spaß beim networken."); ?>"
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