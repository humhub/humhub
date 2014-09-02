<li>
    <!-- load modal confirm widget -->
    <?php $this->widget('application.widgets.ModalConfirmWidget', array(
        'uniqueID' => 'hide-panel-button',
        'title' => '<strong>Remove</strong> tour panel',
        'message' => 'This action will remove the tour panel from your dashboard. You can reactivate it at<br>Account settings <i class="fa fa-caret-right"></i> Settings.',
        'buttonTrue' => 'Ok',
        'buttonFalse' => 'Cancel',
        'linkContent' => '<i class="fa fa-eye-slash"></i> '. Yii::t('TourModule.widgets_views_hideTourPanel', ' Remove panel'),
        'linkHref' => $this->createUrl("//tour/tour/hidePanel", array("ajax" => 1)),
        'confirmJS' => '$(".panel-tour").slideToggle("slow")'
    )); ?>

</li>