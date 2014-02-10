<div class="modal-dialog">
    <div class="modal-content">
        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'space-apply-form',
            'enableAjaxValidation' => true,
        ));
        ?>
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"
                id="myModalLabel"><?php echo Yii::t('SpaceModule.base', "Request space membership"); ?></h4>
        </div>
        <div class="modal-body">

            <?php echo Yii::t('SpaceModule.base', 'Your request was successfully submitted to the space administrators.'); ?>

        </div>
        <div class="modal-footer">

            <button type="button" class="btn btn-primary"
                    data-dismiss="modal"><?php echo Yii::t('base', 'Close'); ?></button>
        </div>

        <?php $this->endWidget(); ?>
    </div>

</div>


<script type="text/javascript">

    /*
     * Modal handling by close event
     */
    $('#globalModal').on('hidden.bs.modal', function (e) {

        // Reload whole page (to see changes on it)
        window.location.reload();

        // just close modal and reset modal content to default (shows the loader)
        //$('#globalModal').html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="loader"></div></div></div></div>');
    })
</script>
