<div class="modal" id="welcomeModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog animated fadeIn">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title"
                    id="myModalLabel"><?php echo Yii::t('TourModule.widgets_views_welcome_index', '<strong>Welcome</strong> to %appName%', array('%appName%' => Yii::app()->name)) ?></h4>
            </div>
            <div class="modal-body">
                <?php echo Yii::t('TourModule.widgets_views_welcome_index', '%appName% ist eine Social Network Platform auf welcher du dich mit Anderen vernetzten, kommunizieren und über Spaces zusammenarbeiten kannst. Einen Space musst du dir wie einen virtuellen Raum vorstellen, in welchem du dich mit anderen Usern austauschst.<br><br>Wie das genau funktioniert, erfährst du in den kleinen Tutorials, welche du rechts obem im Getting Started Panel einzeln starten kannst.', array('%appName%' => Yii::app()->name)) ?>

            </div>

            <div class="modal-footer">
                <hr>
                <br>
                <a href="javascript:welcomeModalSeen();startInterfaceTour();" class="btn btn-info"><?php echo Yii::t('TourModule.widgets_views_welcome_interface', 'Erste Tour starten'); ?></a> <a class="btn btn-primary"
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
                title: "<?php echo Yii::t('TourModule.widgets_views_welcome_interface', '<strong>Dashboard</strong>'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_welcome_interface', "Du befindest dich gerade auf der Einstiegsseite der Platform, dem sogenannten Dashboard.<br><br>Hier erhältst du eine Übersicht über die neusten Inhalte und Aktivitäten aus allen Spaces in welchen du Mitglied bist, sowie von allen Usern, welchen du folgst."); ?>"
            },
            {
                element: "#icon-notifications",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Notification</strong> menu'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'In einem Social Network können schon mal sehr viele Informationen geteilt werden.<br><br>Um den Überblick zu behalten, wirst du über dieses Menü nur über neue Aktivitäten und Inhalte informiert, welche dich direkt betreffen.'); ?>",
                placement: "bottom"
            },
            {
                element: ".dropdown.account",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Account</strong> menu'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'Über das Account-Menu kannst du deine Einstellungen ändern und dein öffentliches Benutzer-Profil pflegen.'); ?>",
                placement: "bottom"
            },
            {
                element: "#topbar-second",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Das</strong> Hauptmenü'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'Dies ist das Hauptmenü über welches du Zugriff auf alle Bereiche der Platform hast.'); ?><?php if (Yii::app()->user->isAdmin() == true) { echo Yii::t('TourModule.widgets_views_index', '<br><br>Abhängig von den installierten Modulen, können diese das Hauptmenü um neue Einträge erweitern.'); } ?>",
                placement: "bottom"
            },
            {
                element: "#space-menu",
                title: "<?php echo Yii::t('TourModule.widgets_views_index', '<strong>Space</strong> Auswahl'); ?>",
                content: "<?php echo Yii::t('TourModule.widgets_views_index', 'Dies ist das wichtigste und wird wohl dein am meinsten genutztes Menü.<br><br>Hier hast du Zugriff auf alle deine Spaces, in welchen du Mitglied bist und kannst auch neue Spaces erstellen.<br><br>Wie Spaces genau funktionieren, erfährst du im nächsten Tutorial.'); ?>",
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
        });
    }

</script>