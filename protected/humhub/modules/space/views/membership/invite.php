<?php

use yii\bootstrap\ActiveForm;
?>
<div class="modal-dialog modal-dialog-small animated fadeIn">
    <div class="modal-content">
        <?php $form = ActiveForm::begin(); ?>
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"
                id="myModalLabel"><?php echo Yii::t('SpaceModule.views_space_invite', '<strong>Invite</strong> members'); ?></h4>
        </div>
        <div class="modal-body">

            <br/>

            <?php if ($canInviteExternal) : ?>
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

            <div class="tab-content">
                <div class="tab-pane active" id="internal">


                    <?php echo Yii::t('SpaceModule.views_space_invite', 'To invite users to this space, please type their names below to find and pick them.'); ?>

                    <br/><br/>

                    <?php echo $form->field($model, 'invite')->textInput(['id' => 'invite'])->label(false); ?>
                    <?php
                    // attach mention widget to it
                    echo humhub\modules\user\widgets\UserPicker::widget(array(
                        'inputId' => 'invite',
                        'model' => $model, // CForm Instanz
                        'attribute' => 'invite',
                        'userSearchUrl' => Yii::$app->controller->getSpace()->createUrl('/space/membership/search-invite'),
                        'placeholderText' => Yii::t('SpaceModule.views_space_invite', 'Add an user'),
                        'focus' => true,
                    ));
                    ?>

                </div>
                <?php if ($canInviteExternal) : ?>
                    <div class="tab-pane" id="external">
                        <?php echo Yii::t('SpaceModule.views_space_invite', 'You can also invite external users, which are not registered now. Just add their e-mail addresses separated by comma.'); ?>
                        <br/><br/>
                        <div class="form-group">
                            <?php echo $form->field($model, 'inviteExternal')->textArea(['rows' => '3', 'placeholder' => Yii::t('SpaceModule.views_space_invite', 'Email addresses'), 'id' => 'email_invite'])->label(false); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>


        </div>
        <div class="modal-footer">

            <?php
            echo \humhub\widgets\AjaxButton::widget([
                'label' => Yii::t('SpaceModule.views_space_invite', 'Send'),
                'ajaxOptions' => [
                    'type' => 'POST',
                    'beforeSend' => new yii\web\JsExpression('function(){ setModalLoader(); }'),
                    'success' => new yii\web\JsExpression('function(html){ $("#globalModal").html(html); }'),
                    'url' => $space->createUrl('/space/membership/invite'),
                ],
                'htmlOptions' => [
                    'class' => 'btn btn-primary'
                ]
            ]);
            ?>
            <button type="button" class="btn btn-primary"
                    data-dismiss="modal"><?php echo Yii::t('SpaceModule.views_space_invite', 'Close'); ?></button>

            <?php echo \humhub\widgets\LoaderWidget::widget(['id' => 'invite-loader', 'cssClass' => 'loader-modal hidden']); ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

</div>


<script type="text/javascript">

// Shake modal after wrong validation
<?php if ($model->hasErrors()) : ?>
        $('.modal-dialog').removeClass('fadeIn');
        $('.modal-dialog').addClass('shake');

        // check if there is an error at the second tab
    <?php if ($model->hasErrors('inviteExternal')) : ?>
            // show tab external tab
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