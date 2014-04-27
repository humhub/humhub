<div class="modal-dialog">
    <div class="modal-content">
        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'invite-message-form',
            'enableAjaxValidation' => false,
        ));
        ?>
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"
                id="myModalLabel"><?php echo Yii::t("Message.Module", "Add more participants to your conversation..."); ?></h4>
        </div>
        <div class="modal-body">


            <?php //echo $form->errorSummary($inviteForm); ?>



            <?php echo $form->textField($inviteForm, 'recipient', array('id' => 'addUserFrom_mail')); ?>
            <?php echo $form->error($inviteForm, 'recipient'); ?>
            <?php
            // attach mention widget to it
            $this->widget('application.modules_core.user.widgets.UserPickerWidget', array(
                'inputId' => 'addUserFrom_mail',
                'model' => $inviteForm, // CForm Instanz
                'attribute' => 'recipient',
                'userGuid' => Yii::app()->user->guid,
                'focus' => true,
            ));
            ?>

        </div>
        <div class="modal-footer">

            <?php echo HHtml::ajaxButton(Yii::t('SpaceModule.base', 'Send'), array('//mail/mail/adduser', 'id' => $message->id), array(
                'type' => 'POST',
                'beforeSend' => 'function(){ $("#adduser-loader").removeClass("hidden"); }',
                'success' => 'function(html){ $("#globalModal").html(html); }',
            ), array('class' => 'btn btn-primary'));
            ?>


            <button type="button" class="btn btn-primary"
                    data-dismiss="modal"><?php echo Yii::t('base', 'Close'); ?></button>

            <div class="col-md-1 modal-loader">
                <div id="adduser-loader" class="loader loader-small hidden"></div>
            </div>
        </div>

        <?php $this->endWidget(); ?>
    </div>

</div>


<script type="text/javascript">


    // set focus to input for space name
    $('#SpaceCreateForm_title').focus();

    /*
     * Modal handling by close event
     */
    $('#globalModal').on('hidden.bs.modal', function (e) {

        // Reload whole page (to see changes on it)
        //window.location.reload();

        // just close modal and reset modal content to default (shows the loader)
        $('#globalModal').html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="loader"></div></div></div></div>');
    })
</script>



