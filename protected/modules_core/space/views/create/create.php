<div class="modal-dialog modal-dialog-small animated fadeIn">
    <div class="modal-content">
        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'space-create-form',
            'enableAjaxValidation' => false,
        ));
        ?>
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"
                id="myModalLabel"><?php echo Yii::t('SpaceModule.base', '<strong>Create</strong> new space'); ?></h4>
        </div>
        <div class="modal-body">

            <div class="text-center">
                <?php //echo Yii::t('SpaceModule.base', 'In spaces you can work, share and communicate with other people<br>about any subjects, projects or anything else.'); ?>
                <?php //echo $form->errorSummary($model); ?>
            </div>
            <hr/>
            <br/>


            <div class="form-group">

                <label>What do you want to call your space?</label>
                <?php print $form->textField($model, 'title', array('class' => 'form-control', 'placeholder' => Yii::t('SpaceModule.base', 'space name'))); ?>
                <?php echo $form->error($model, 'title'); ?>

                <?php $types = array('workspace' => Yii::t('SpaceModule.base', 'Space')); ?>
                <?php print $form->dropDownList($model, 'type', $types, array('class' => 'form-control', 'style' => 'display: none;')); ?>

            </div>
            <div class="form-group">
                <label>Which reason has the space?</label>
                <?php print $form->textArea($model, 'description', array('class' => 'form-control', 'placeholder' => Yii::t('SpaceModule.base', 'space description'))); ?>
            </div>

            <a data-toggle="collapse" id="access-settings-link" href="#collapse-access-settings"
               style="font-size: 11px;"><i class="fa fa-caret-right"></i> Advanced access settings</a>

            <div id="collapse-access-settings" class="panel-collapse collapse">
                <br/>

                <?php $joinPolicies = array(0 => Yii::t('SpaceModule.base', 'Only by invite'), 1 => Yii::t('SpaceModule.base', 'Invite and request'), 2 => Yii::t('SpaceModule.base', 'For everyone')); ?>

                <div class="row">
                    <div class="col-md-6">
                        <label for="">Join Policy</label>
                        <div class="radio">
                            <label class="tt" data-toggle="tooltip" data-placement="top"
                                   title="<?php echo Yii::t('SpaceModule.create', 'Users can be only added<br>by invitation'); ?>">
                                <?php echo $form->radioButton($model, 'join_policy', array('value'=>0, 'uncheckValue'=>null, 'id' => 'invite_radio', 'checked' => ''));?>
                                <?php echo Yii::t('SpaceModule.create', 'Only by invite'); ?>
                            </label>
                        </div>
                        <div class="radio">
                            <label class="tt" data-toggle="tooltip" data-placement="top"
                                   title="<?php echo Yii::t('SpaceModule.create', 'Users can also apply for a<br>membership to this space'); ?>">
                                <?php echo $form->radioButton($model, 'join_policy', array('value'=>1,'uncheckValue'=>null, 'id' => 'request_radio', 'checked' => 'checked'));?>
                                <?php echo Yii::t('SpaceModule.create', ' Invite and request'); ?>
                            </label>
                        </div>
                        <div class="radio">
                            <label class="tt" data-toggle="tooltip" data-placement="top"
                                   title="<?php echo Yii::t('SpaceModule.create', 'Every user can enter your space<br>without your approval'); ?>">
                                <?php echo $form->radioButton($model, 'join_policy', array('value'=>2,'uncheckValue'=>null, 'id' => 'everyone_radio', 'checked' => ''));?>
                                <?php echo Yii::t('SpaceModule.create', 'Everyone can enter'); ?>
                            </label>
                        </div>
                        <br/>
                    </div>
                    <div class="col-md-6">
                        <label for="">Visibility</label>
                        <div class="radio">
                            <label class="tt" data-toggle="tooltip" data-placement="top"
                                   title="<?php echo Yii::t('SpaceModule.create', 'Also non-members can see this<br>space, but have no access'); ?>">
                                <?php echo $form->radioButton($model, 'visibility', array('value'=>1,'uncheckValue'=>null, 'id' => 'public_radio', 'checked' => 'checked'));?>
                                <?php echo Yii::t('SpaceModule.create', ' Public (Visible)'); ?>
                            </label>
                        </div>
                        <div class="radio">
                            <label class="tt" data-toggle="tooltip" data-placement="top"
                                   title="<?php echo Yii::t('SpaceModule.create', 'This space will be hidden<br>for all non-members'); ?>">
                                <?php echo $form->radioButton($model, 'visibility', array('value'=>0,'uncheckValue'=>null, 'id' => 'private_radio', 'checked' => ''));?>
                                <?php echo Yii::t('SpaceModule.create', 'Private (Invisible)'); ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <hr/>
            <br/>
            <?php
            echo HHtml::ajaxButton(Yii::t('SpaceModule.base', 'Create'), array('//space/create/create'), array(
                'type' => 'POST',
                'beforeSend' => 'function(){ jQuery("#create-loader").removeClass("hidden"); }',
                'success' => 'function(html){ $("#globalModal").html(html); }',
            ), array('class' => 'btn btn-primary', 'id' => 'space-create-submit-button'));
            ?>

            <div class="col-md-1 modal-loader">
                <div id="create-loader" class="loader loader-small hidden"></div>
            </div>
        </div>

        <?php $this->endWidget(); ?>
    </div>

</div>


<script type="text/javascript">

    // Replace the standard checkbox and radio buttons
    $('.modal-dialog').find(':checkbox, :radio').flatelements();

    // show Tooltips on elements inside the views, which have the class 'tt'
    //$('.tt').tooltip({html: true});

    // set focus to input for space name
    $('#SpaceCreateForm_title').focus();

    // Shake modal after wrong validation
    <?php if ($form->errorSummary($model) != null) { ?>
    $('.modal-dialog').removeClass('fadeIn');
    $('.modal-dialog').addClass('shake');
    <?php } ?>

    $('#collapse-access-settings').on('show.bs.collapse', function () {
        // change link arrow
        $('#access-settings-link i').removeClass('fa-caret-right');
        $('#access-settings-link i').addClass('fa-caret-down');
    })

    $('#collapse-access-settings').on('hide.bs.collapse', function () {
        // change link arrow
        $('#access-settings-link i').removeClass('fa-caret-down');
        $('#access-settings-link i').addClass('fa-caret-right');
    })


    /*
     * Modal handling by close event
     */
    $('#globalModal').on('hidden.bs.modal', function (e) {

        // Reload whole page (to see changes on it)
        //window.location.reload();

        // just close modal and reset modal content to default (shows the loader)
        $('#globalModal').html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="loader"></div></div></div></div>');
    })

    // prevent enter key and simulate ajax button submit click
    $(document).ready(function() {
        $(window).keydown(function(event){
            if(event.keyCode == 13) {
                event.preventDefault();
                $('#space-create-submit-button').click();
                //return false;
            }
        });
    });

</script>



