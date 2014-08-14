<script type="text/javascript">


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
            content: "<?php echo Yii::t('TourModule.widgets_views_profile', "Dies ist dein öffentliches User-Profil, welches für alle registrierte User zugänglich ist."); ?>"
        },
        {
            element: "#user-profile-image",
            title: "<?php echo Yii::t('TourModule.widgets_views_profile', '<strong>Profile</strong> images'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_profile', ''); ?>",
            placement: "right"
        },
        {
            element: ".controls-account",
            title: "<?php echo Yii::t('TourModule.widgets_views_profile', '<strong>Edit</strong> profile'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_profile', ''); ?>",
            placement: "left"
        },
        {
            element: ".profile-nav-container",
            title: "<?php echo Yii::t('TourModule.widgets_views_profile', '<strong>Profile</strong> menu'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_profile', ''); ?>",
            placement: "right"
        },
        {
            element: "#contentFormBody",
            title: "<?php echo Yii::t('TourModule.widgets_views_profile', '<strong>Profile</strong> stream'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_profile', 'Jedes Profil verfügt ebensfall über eine eigene Pinnwand. User welcher dir Folgen, sehen diese Beiträge auch auf ihrem Dashbaord.'); ?>",
            placement: "bottom"
        },
        {
            element: ".profile-sidebar-container",
            title: "<?php echo Yii::t('TourModule.widgets_views_profile', '<strong>Profile</strong> widgets'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_profile', 'Mithile von Widget werden weitere Informationen zum User angezeigt.<br><br>Die Panels werden aber erst angezeigt, wenn auch Informationen zu dem User verfügbar sind.'); ?>",
            placement: "left"
        },
        {
            orphan: true,
            backdrop: true,
            title: "<?php echo Yii::t('TourModule.widgets_views_profile', '<strong>Finished</strong>'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_profile', "Hiermit hast du das Tutorial für das User-Profil erfolgreich abgeschlossen."); ?>"
        },


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
        });
    }

</script>