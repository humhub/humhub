<?php

use yii\helpers\Url;

$this->context->loadResources($this);
?>
<script type="text/javascript">
    
    $( document ).ready(function() {
        // Create a new tour
        var administrationTour = new Tour({
            storage: false,
            template: '<div class="popover tour"> <div class="arrow"></div> <h3 class="popover-title"></h3> <div class="popover-content"></div> <div class="popover-navigation"> <div class="btn-group"> <button class="btn btn-sm btn-default" data-role="prev"><?php echo Yii::t('TourModule.base', '« Prev'); ?></button> <button class="btn btn-sm btn-default" data-role="next"><?php echo Yii::t('TourModule.base', 'Next »'); ?></button>  </div> <button class="btn btn-sm btn-default" data-role="end"><?php echo Yii::t('TourModule.base', 'End guide'); ?></button> </div> </div>',
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
                title: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_administration', '<strong>Administration</strong>')); ?>,
                content: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_administration', "As an admin, you can manage the whole platform from here.<br><br>Apart from the modules, we are not going to go into each point in detail here, as each has its own short description elsewhere.")); ?>
            },
            {
                element: ".list-group-item.modules",
                title: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_administration', '<strong>Modules</strong>')); ?>,
                content: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_administration', 'You are currently in the tools menu. From here you can access the HumHub online marketplace, where you can install an ever increasing number of tools on-the-fly.<br><br>As already mentioned, the tools increase the features available for your space.')); ?>,
                placement: "right"
            },
            {
                orphan: true,
                backdrop: true,
                title: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_administration', "<strong>Hurray!</strong> That's all for now.")); ?>,
                content: <?php echo json_encode(Yii::t('TourModule.widgets_views_guide_administration', 'You have now learned about all the most important features and settings and are all set to start using the platform.<br><br>We hope you and all future users will enjoy using this site. We are looking forward to any suggestions or support you wish to offer for our project. Feel free to contact us via www.humhub.org.<br><br>Stay tuned. :-)')); ?>
            }

        ]);

        // Initialize tour plugin
        administrationTour.init();

        // start the tour
        administrationTour.restart();

    });
    /**
     * Set tour as seen
     */
    function tourCompleted() {
        // load user spaces
        $.ajax({
            'url': '<?php echo Url::to(['/tour/tour/tour-completed', 'section' => 'administration']); ?>',
            'cache': false,
            'data': jQuery(this).parents("form").serialize()
        });
    }

</script>