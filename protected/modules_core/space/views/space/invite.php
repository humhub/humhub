<div class="modal-dialog">
    <div class="modal-content">
        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'space-invite-form',
            'enableAjaxValidation' => false,
        ));
        ?>
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo Yii::t('SpaceModule.base', 'Invite new user'); ?></h4>
        </div>
        <div class="modal-body">

            <?php //echo $form->errorSummary($model); ?>

            <?php //echo $form->labelEx($model, 'invite'); ?>
            <?php echo Yii::t('SpaceModule.base', 'Invite user to this space, from the community or by email address...'); ?>
            <br><br>
            <?php echo $form->textField($model, 'invite', array('class' => 'form-control', 'id' => 'invite')); ?>
            <?php echo $form->error($model, 'invite'); ?>

            <?php
            // attach mention widget to it
            $this->widget('application.modules_core.user.widgets.UserPickerWidget', array(
                'inputId' => 'invite',
                'model' => $model, // CForm Instanz
                'attribute' => 'invite',
                'placeholderText' => Yii::t('SpaceModule.base', 'Add a user'),
                'focus' => true,
            ));
            ?>
            <?php if (HSetting::Get('internalUsersCanInvite', 'authentication_internal')) : ?>
                <div class="form-group">
                    <?php echo $form->label($model, 'inviteExternal'); ?>
                    <?php echo $form->textField($model, 'inviteExternal', array('class' => 'form-control', 'id' => 'invite', 'placeholder' => 'Email address')); ?>
                    <?php echo $form->error($model, 'inviteExternal'); ?>
                </div>
            <?php endif; ?>

        </div>
        <div class="modal-footer">

            <?php echo HHtml::ajaxButton(Yii::t('SpaceModule.base', 'Send'), array('//space/space/invite', 'sguid' => $space->guid), array(
                'type' => 'POST',
                'beforeSend' => 'function(){ $("#invite-loader").removeClass("hidden"); }',
                'success' => 'function(html){ $("#globalModal").html(html); }',
            ), array('class' => 'btn btn-primary', 'id' => 'inviteBtn'));
            ?>
            <button type="button" class="btn btn-primary"
                    data-dismiss="modal"><?php echo Yii::t('base', 'Close'); ?></button>

            <div class="col-md-1 modal-loader">
                <div id="invite-loader" class="loader loader-small hidden"></div>
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