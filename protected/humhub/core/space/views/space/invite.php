<div class="modal-dialog modal-dialog-small animated fadeIn">
    <div class="modal-content">
        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'space-invite-form',
            'enableAjaxValidation' => false,
        ));
        ?>
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"
                id="myModalLabel"><?php echo Yii::t('SpaceModule.views_space_invite', '<strong>Invite</strong> members'); ?></h4>
        </div>
        <div class="modal-body">

            <br/>

            <?php if (HSetting::Get('internalUsersCanInvite', 'authentication_internal')) : ?>
                <div class="text-center">
                    <ul id="tabs" class="nav nav-tabs tabs-center" data-tabs="tabs">
                        <li class="active tab-internal"><a href="#internal"
                                                           data-toggle="tab"><?php echo Yii::t('SpaceModule.views_space_invite', 'Pick users'); ?></a>
                        </li>
                        <li class="tab-external"><a href="#external"
                                                    data-toggle="tab"><?php echo Yii::t('SpaceModule.views_space_invite', 'Invite by email'); ?></a>
                        </li>
                    </ul>
                </div>
                <br/>
            <?php endif; ?>

            <?php echo $form->error($model, 'invite'); ?>

            <div class="tab-content">
                <div class="tab-pane active" id="internal">


                    <?php echo Yii::t('SpaceModule.views_space_invite', 'To invite users to this space, please type their names below to find and pick them.'); ?>

                    <br/><br/>
                    <?php echo $form->textField($model, 'invite', array('class' => 'form-control', 'id' => 'invite')); ?>
                    <?php
                    // attach mention widget to it
                    $this->widget('application.modules_core.user.widgets.UserPickerWidget', array(
                        'inputId' => 'invite',
                        'model' => $model, // CForm Instanz
                        'attribute' => 'invite',
                        'placeholderText' => Yii::t('SpaceModule.views_space_invite', 'Add an user'),
                        'focus' => true,
                    ));
                    ?>

                </div>
                <?php if (HSetting::Get('internalUsersCanInvite', 'authentication_internal')) : ?>
                    <div class="tab-pane" id="external">
                        <?php echo Yii::t('SpaceModule.views_space_invite', 'You can also invite external users, which are not registered now. Just add their e-mail addresses separated by comma.'); ?>
                        <br/><br/>

                        <div class="form-group">
                            <?php //echo $form->label($model, 'inviteExternal'); ?>
                            <?php echo $form->textArea($model, 'inviteExternal', array('class' => 'form-control', 'rows' => '3', 'id' => 'email_invite', 'placeholder' => Yii::t('SpaceModule.views_space_invite', 'Email addresses'))); ?>
                            <?php echo $form->error($model, 'inviteExternal'); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>


        </div>
        <div class="modal-footer">

            <?php echo HHtml::ajaxButton(Yii::t('SpaceModule.views_space_invite', 'Send'), array('//space/space/invite', 'sguid' => $space->guid), array(
                'type' => 'POST',
                'beforeSend' => 'function(){ setModalLoader(); }',
                'success' => 'function(html){ $("#globalModal").html(html); }',
            ), array('class' => 'btn btn-primary', 'id' => 'inviteBtn'));
            ?>
            <button type="button" class="btn btn-primary"
                    data-dismiss="modal"><?php echo Yii::t('SpaceModule.views_space_invite', 'Close'); ?></button>

            <div id="invite-loader" class="loader loader-modal hidden">
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

    // Shake modal after wrong validation
    <?php if ($form->errorSummary($model) != null) : ?>
    $('.modal-dialog').removeClass('fadeIn');
    $('.modal-dialog').addClass('shake');

    // check if there is an error at the second tab
    <?php if ($form->error($model, 'inviteExternal') != null) : ?>

    // show tab
    $('#tabs a:last').tab('show');

    <?php endif; ?>

    <?php endif; ?>

    $('.tab-internal a').on('shown.bs.tab', function (e) {
        $('#invite_tag_input_field').focus();
    })

    $('.tab-external a').on('shown.bs.tab', function (e) {
        $('#email_invite').focus();
    })


</script>