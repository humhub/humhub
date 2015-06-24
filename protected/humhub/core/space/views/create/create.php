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
                id="myModalLabel"><?php echo Yii::t('SpaceModule.views_create_create', '<strong>Create</strong> new space'); ?></h4>
        </div>
        <div class="modal-body">

            <hr/>
            <br/>


            <div class="form-group">

                <label><?php echo Yii::t('SpaceModule.views_create_create', 'How you want to name your space?'); ?></label>
                <?php print $form->textField($model, 'name', array('class' => 'form-control', 'placeholder' => Yii::t('SpaceModule.views_create_create', 'space name'))); ?>
                <?php echo $form->error($model, 'name'); ?>

            </div>
            <div class="form-group">
                <label><?php echo Yii::t('SpaceModule.views_create_create', 'Please write down a small description for other users.'); ?></label>
                <?php print $form->textArea($model, 'description', array('class' => 'form-control', 'rows' => '3', 'placeholder' => Yii::t('SpaceModule.views_create_create', 'space description'))); ?>
            </div>

            <a data-toggle="collapse" id="access-settings-link" href="#collapse-access-settings"
               style="font-size: 11px;"><i
                    class="fa fa-caret-right"></i> <?php echo Yii::t('SpaceModule.views_create_create', 'Advanced access settings'); ?>
            </a>

            <div id="collapse-access-settings" class="panel-collapse collapse">
                <br/>

                <?php $joinPolicies = array(0 => Yii::t('SpaceModule.views_create_create', 'Only by invite'), 1 => Yii::t('SpaceModule.views_create_create', 'Invite and request'), 2 => Yii::t('SpaceModule.views_create_create', 'For everyone')); ?>

                <div class="row">
                    <div class="col-md-6">
                        <label for=""><?php echo Yii::t('SpaceModule.views_create_create', 'Join Policy'); ?></label>

                        <div class="radio">
                            <label class="tt" data-toggle="tooltip" data-placement="top"
                                   title="<?php echo Yii::t('SpaceModule.views_create_create', 'Users can be only added<br>by invitation'); ?>">
                                <?php echo $form->radioButton($model, 'join_policy', array('value' => 0, 'uncheckValue' => null, 'id' => 'invite_radio', 'checked' => (HSetting::Get('defaultJoinPolicy', 'space') == 0) ? 'checked' : '')); ?>
                                <?php echo Yii::t('SpaceModule.base', 'Only by invite'); ?>
                            </label>
                        </div>
                        <div class="radio">
                            <label class="tt" data-toggle="tooltip" data-placement="top"
                                   title="<?php echo Yii::t('SpaceModule.views_create_create', 'Users can also apply for a<br>membership to this space'); ?>">
                                <?php echo $form->radioButton($model, 'join_policy', array('value' => 1, 'uncheckValue' => null, 'id' => 'request_radio', 'checked' => (HSetting::Get('defaultJoinPolicy', 'space') == 1) ? 'checked' : '')); ?>
                                <?php echo Yii::t('SpaceModule.base', ' Invite and request'); ?>
                            </label>
                        </div>
                        <div class="radio">
                            <label class="tt" data-toggle="tooltip" data-placement="top"
                                   title="<?php echo Yii::t('SpaceModule.views_create_create', 'Every user can enter your space<br>without your approval'); ?>">
                                <?php echo $form->radioButton($model, 'join_policy', array('value' => 2, 'uncheckValue' => null, 'id' => 'everyone_radio', 'checked' => (HSetting::Get('defaultJoinPolicy', 'space') == 2) ? 'checked' : '')); ?>
                                <?php echo Yii::t('SpaceModule.base', 'Everyone can enter'); ?>
                            </label>
                        </div>
                        <br/>
                    </div>
                    <div class="col-md-6">
                        <label for=""><?php echo Yii::t('SpaceModule.views_create_create', 'Visibility'); ?></label>

                        <?php if (Yii::app()->user->canCreatePublicSpace() && Yii::app()->user->canCreatePrivateSpace()): ?>

                            <?php if (HSetting::Get('allowGuestAccess', 'authentication_internal')) : ?>
                                <div class="radio">
                                    <label class="tt" data-toggle="tooltip" data-placement="top">
                                        <?php echo $form->radioButton($model, 'visibility', array('value' => 2, 'uncheckValue' => null, 'id' => 'public_radio', 'checked' => (HSetting::Get('defaultVisibility', 'space') == 2) ? 'checked' : '')); ?>
                                        <?php echo Yii::t('SpaceModule.base', 'Public (Members & Guests)'); ?>
                                    </label>
                                </div>
                            <?php endif; ?>

                            <div class="radio">
                                <label class="tt" data-toggle="tooltip" data-placement="top"
                                       title="<?php echo Yii::t('SpaceModule.views_create_create', 'Also non-members can see this<br>space, but have no access'); ?>">
                                    <?php echo $form->radioButton($model, 'visibility', array('value' => 1, 'uncheckValue' => null, 'id' => 'public_radio', 'checked' => (HSetting::Get('defaultVisibility', 'space') == 1) ? 'checked' : '')); ?>
                                    <?php echo Yii::t('SpaceModule.base', 'Public (Members only)'); ?>
                                </label>
                            </div>
                            <div class="radio">
                                <label class="tt" data-toggle="tooltip" data-placement="top"
                                       title="<?php echo Yii::t('SpaceModule.views_create_create', 'This space will be hidden<br>for all non-members'); ?>">
                                    <?php echo $form->radioButton($model, 'visibility', array('value' => 0, 'uncheckValue' => null, 'id' => 'private_radio', 'checked' => (HSetting::Get('defaultVisibility', 'space') == 0) ? 'checked' : '')); ?>
                                    <?php echo Yii::t('SpaceModule.base', 'Private (Invisible)'); ?>
                                </label>
                            </div>
                        <?php elseif (Yii::app()->user->canCreatePublicSpace()): ?>
                            <div>
                                <?php echo Yii::t('SpaceModule.views_create_create', 'Public (Visible)'); ?>
                            </div>
                        <?php
                        elseif (Yii::app()->user->canCreatePrivateSpace()): ?>
                            <div>
                                <?php echo Yii::t('SpaceModule.views_create_create', 'Private (Invisible)'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <hr/>
            <br/>
            <?php
            echo HHtml::ajaxButton(Yii::t('SpaceModule.views_create_create', 'Create'), array('//space/create/create'), array(
                'type' => 'POST',
                'beforeSend' => 'function(){ setModalLoader(); }',
                'success' => 'function(html){ $("#globalModal").html(html); }',
            ), array('class' => 'btn btn-primary', 'id' => 'space-create-submit-button'));
            ?>

            <div id="create-loader" class="loader loader-modal hidden">
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

    // Replace the standard checkbox and radio buttons
    $('.modal-dialog').find(':checkbox, :radio').flatelements();

    // show Tooltips on elements inside the views, which have the class 'tt'
    //$('.tt').tooltip({html: true});

    // set focus to input for space name
    $('#Space_name').focus();

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

    // prevent enter key and simulate ajax button submit click
    $(document).ready(function () {
        $(window).keydown(function (event) {
            if (event.keyCode == 13) {
                event.preventDefault();
                $('#space-create-submit-button').click();
                //return false;
            }
        });
    });

</script>



