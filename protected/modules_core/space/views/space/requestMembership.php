<div class="modal-dialog animated fadeIn">
    <div class="modal-content">
        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'space-apply-form',
            'enableAjaxValidation' => false,
        ));
        ?>
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"
                id="myModalLabel"><?php echo Yii::t('SpaceModule.views_space_requestMembership', "<strong>Request</strong> space membership"); ?></h4>
        </div>
        <div class="modal-body">

            <?php echo Yii::t('SpaceModule.views_space_requestMembership', 'Please shortly introduce yourself, to become an approved member of this space.'); ?>

            <br/>
            <br/>



            <?php //echo $form->labelEx($model, 'message'); ?>
            <?php echo $form->textArea($model, 'message', array('rows' => '8', 'class' => 'form-control', 'id' => 'request-message')); ?>
            <?php echo $form->error($model, 'message'); ?>


        </div>
        <div class="modal-footer">
            <hr/>
            <?php
            echo CHtml::ajaxButton(Yii::t('SpaceModule.views_space_requestMembership', 'Send'), array('//space/space/requestMembershipForm', 'sguid' => $space->guid), array(
                'type' => 'POST',
                'beforeSend' => 'function(){ setModalLoader(); }',
                'success' => 'function(html){ $("#globalModal").html(html); }',
            ), array('class' => 'btn btn-primary'));
            ?>


            <button type="button" class="btn btn-primary"
                    data-dismiss="modal"><?php echo Yii::t('SpaceModule.views_space_requestMembership', 'Close'); ?></button>

            <div id="send-loader" class="loader loader-modal hidden">
                <div class="sk-spinner sk-spinner-three-bounce">
                    <div class="sk-bounce1"></div>
                    <div class="sk-bounce2"></div>
                    <div class="sk-bounce3"></div>
                </div>
            </div>

        </div>

        <?php $this->endWidget(); ?>
    </div>

</div>


<script type="text/javascript">

    // set focus to input field
    $('#request-message').focus()

    // Shake modal after wrong validation
    <?php if ($form->errorSummary($model) != null) { ?>
    $('.modal-dialog').removeClass('fadeIn');
    $('.modal-dialog').addClass('shake');
    <?php } ?>

</script>
