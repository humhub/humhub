<script type="text/javascript">


    // Create a new tour
    var administrationTour = new Tour({
        storage: false,
        template: '<div class="popover tour"> <div class="arrow"></div> <h3 class="popover-title"></h3> <div class="popover-content"></div> <div class="popover-navigation"> <div class="btn-group"> <button class="btn btn-sm btn-default" data-role="prev"><?php echo Yii::t('TourModule.base', '« Prev'); ?></button> <button class="btn btn-sm btn-default" data-role="next"><?php echo Yii::t('TourModule.base', 'Next »'); ?></button>  </div> <button class="btn btn-sm btn-default" data-role="end"><?php echo Yii::t('TourModule.base', 'End tour'); ?></button> </div> </div>',
        name: 'administration',
        onEnd: function (tour) {
            tourCompleted();
        }
    });


    // Add tour steps
    administrationTour.addSteps([
        {
            orphan: true,
            backdrop: true,
            title: "<?php echo Yii::t('TourModule.widgets_views_administration', '<strong>Administration</strong>'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_administration', "Als Administrator kannst du hier die komplette Platform verwalten.<br><br>Im oberen Teil der linken Navigation kannst du die User und Spaces verwalten und im unteren Teil Einstellungen zur Platform vornehmen."); ?>"
        },
        {
            element: "#user-profile-image",
            title: "<?php echo Yii::t('TourModule.widgets_views_administration', '<strong>Profile</strong> images'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_administration', ''); ?>",
            placement: "right"
        },
        {
            element: ".list-group-item.modules",
            title: "<?php echo Yii::t('TourModule.widgets_views_administration', '<strong>Modules</strong>'); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_administration', 'Du hast gerade den Menüpunkt Modules ausgewählt. Hier erhälst du Zugriff auf den Online-Marketplace, von welchem du eine wachsende Anzahl von Modulen aus der Community installieren kannst.'); ?>",
            placement: "right"
        },
        {
            orphan: true,
            backdrop: true,
            title: "<?php echo Yii::t('TourModule.widgets_views_administration', "<strong>Hurray!</strong> We're done."); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_administration', 'This have been the most important things you should know before using the platform.<br><br>We hope, that you and the future users will have a good experience and fun with the platform.<br><br>We are always looking forward for every suggestion or any kind of help to support the project. Feel free to contact us via www.humhub.org.<br><br>Stay tuned. :)'); ?>"
        }

    ]);

    // Initialize tour plugin
    administrationTour.init();

    // start the tour
    administrationTour.restart();


    /**
     * Set tour as seen
     */
    function tourCompleted() {
        // load user spaces
        $.ajax({
            'url': '<?php echo Yii::app()->createAbsoluteUrl('//tour/tour/TourCompleted', array('section' => 'administration')); ?>',
            'cache': false,
            'data': jQuery(this).parents("form").serialize()
        }).done(function () {
            // redirect to dashboard
            window.location.href="<?php echo Yii::app()->createUrl('//dashboard/dashboard'); ?>";
        });
    }

</script>