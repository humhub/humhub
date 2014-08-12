<div class="modal" id="welcomeModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog animated fadeIn">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title"
                    id="myModalLabel"><?php echo Yii::t('TourModule.widgets_views_welcome_index', '<strong>Welcome</strong> to %appName%', array('%appName%' => Yii::app()->name)) ?></h4>
            </div>
            <div class="modal-body">
                <?php echo Yii::t('TourModule.widgets_views_welcome_index', '') ?>

            </div>

            <div class="modal-footer">
                <hr>
                <br>
                <a href="javascript:welcomeModalSeen();startInterfaceTour();" class="btn btn-info"><?php echo Yii::t('TourModule.widgets_views_welcome_interface', 'Discover the interface'); ?></a> <a class="btn btn-primary"
                                                                                       href="javascript:welcomeModalSeen();"
                                                                                       data-dismis="modal"><?php echo Yii::t('TourModule.widgets_views_welcome_interface', 'Close'); ?></a>
            </div>
        </div>
    </div>
</div>
<!-- end: Modal -->


<script type="text/javascript">

    <?php

    // check if the welcome screen was already shown
    $welcome = Yii::app()->user->getModel()->getSetting("welcome", "tour");

    // If not ...
    if ($welcome != 1) :
    ?>

    // show welcome modal
    $('#welcomeModal').modal("show");

    <?php endif; ?>

    /**
     * Set welcome modal as seen
     */
    function welcomeModalSeen() {
        // load user spaces
        $.ajax({
            'url': '<?php echo Yii::app()->createAbsoluteUrl('tour/tour/TourCompleted', array('section' => 'welcome')); ?>',
            'cache': false,
            'data': jQuery(this).parents("form").serialize()
        }).done(function () {
            // hide modal
            $('#welcomeModal').modal('hide')
        });
    }

</script>


<script type="text/javascript">

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
                title: "<?php echo Yii::t('TourModule.widgets_views_welcome_interface', '<strong>Welcome</strong> to %appName%', array('%appName%' => Yii::app()->name)); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_welcome_interface', "This is a brief introduction of %appName% to give you an overview about the most important functions... Let's go!", array('%appName%' => Yii::app()->name)); ?>"
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
        });
    }

</script>