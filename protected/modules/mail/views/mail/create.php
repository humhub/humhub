<div class="modal-dialog">
    <div class="modal-content">
        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'create-message-form',
            'enableAjaxValidation' => false,
        ));
        ?>

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"
                id="myModalLabel"><?php echo Yii::t("Message.Module", "New message"); ?></h4>
        </div>
        <div class="modal-body">


            <?php echo $form->errorSummary($model); ?>

            <?php echo $form->labelEx($model, 'recipient'); ?>
            <?php echo $form->textField($model, 'recipient', array('class' => 'span12', 'id' => 'recipient')); ?>

            <?php
            // build a standard dropdown list
            //    echo $form->dropDownList(
            //        $group, 'admins', array(), array(
            //        'multiple' => true,
            //        'id' => 'user_select',
            //        'class' => 'user span12',
            //        'data-placeholder' => Yii::t('UserModule.base', 'Add user...'),
            //    ));
            // attach mention widget to it
            $this->widget('application.modules_core.user.widgets.UserPickerWidget', array(
                'inputId' => 'recipient',
                'model' => $model, // CForm Instanz
                'attribute' => 'recipient',
                'userGuid' => Yii::app()->user->guid,
                'focus' => true,
            ));
            ?>

            <div class="form-group">
                <?php echo $form->labelEx($model, 'title'); ?>
                <?php echo $form->textField($model, 'title', array('class' => 'form-control')); ?>
                <?php echo $form->error($model, 'title'); ?>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model, 'message'); ?>
                <?php echo $form->textArea($model, 'message', array('class' => 'form-control', 'rows' => '7')); ?>
                <?php echo $form->error($model, 'message'); ?>
            </div>

        </div>
        <div class="modal-footer">
            <hr/>
            <?php echo HHtml::ajaxButton(Yii::t('SpaceModule.base', 'Send'), array('//mail/mail/create'), array(
                'type' => 'POST',
                'beforeSend' => 'function(){ $("#create-message-loader").removeClass("hidden"); }',
                'success' => 'function(html){ $("#globalModal").html(html); }',
            ), array('class' => 'btn btn-primary'));
            ?>


            <button type="button" class="btn btn-primary"
                    data-dismiss="modal"><?php echo Yii::t('base', 'Close'); ?></button>

            <div class="col-md-1 modal-loader">
                <div id="create-message-loader" class="loader loader-small hidden"></div>
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