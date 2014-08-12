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
            // step 0
            orphan: true,
            backdrop: true,
            title: "<?php echo Yii::t('TourModule.widgets_views_administration', '<strong>Welcome</strong> to %appName%', array('%appName%' => Yii::app()->name)); ?>",
            content: "<?php echo Yii::t('TourModule.widgets_views_administration', "This is a brief introduction of %appName% to give you an overview about the most important functions... Let's go!", array('%appName%' => Yii::app()->name)); ?>"
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