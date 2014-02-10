<div class="modal-dialog">
    <div class="modal-content">
        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'space-create-form',
            'enableAjaxValidation' => false,
        ));
        ?>
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel"><?php echo Yii::t('SpaceModule.base', 'Create new space'); ?></h4>
        </div>
        <div class="modal-body">


            <?php echo Yii::t('SpaceModule.base', 'Please enter the name of your new space. You can edit the options on the next page.'); ?>
            <br><br>
            <?php print $form::textField($model, 'title', array('class' => 'form-control', 'placeholder' => Yii::t('SpaceModule.base', 'space name'))); ?>
            <?php echo $form->error($model, 'title'); ?>

            <?php $types = array('workspace' => Yii::t('SpaceModule.base', 'Space')); ?>
            <?php print $form::dropDownList($model, 'type', $types, array('class' => 'form-control', 'style' => 'display: none;')); ?>

        </div>
        <div class="modal-footer">

            <?php
            echo HHtml::ajaxButton(Yii::t('SpaceModule.base', 'Create'), array('//space/create/create'), array(
                'type' => 'POST',
                'beforeSend' => 'function(){ jQuery("#create-loader").removeClass("hidden"); }',
                'success' => 'function(html){ $("#globalModal").html(html); }',
            ), array('class' => 'btn btn-primary'));
            ?><button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo Yii::t('base', 'Close'); ?></button>

            <div class="col-md-1 modal-loader">
                <div id="create-loader" class="loader loader-small hidden"></div>
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



