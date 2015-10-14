<?php

use yii\widgets\ActiveForm;
use yii\helpers\Url;
use humhub\models\Setting;
use humhub\modules\space\permissions\CreatePublicSpace;
use humhub\modules\space\permissions\CreatePrivateSpace;

?>
<div class="modal-dialog modal-dialog-small animated fadeIn">
    <div class="modal-content">
        <?php $form = ActiveForm::begin(); ?>
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"
                id="myModalLabel"><?php echo Yii::t('SpaceModule.views_create_create', '<strong>Create</strong> new space'); ?></h4>
        </div>
        <div class="modal-body">

            <hr>
            <br>

            <?php echo $form->field($model, 'name')->textInput(['placeholder' => Yii::t('SpaceModule.views_create_create', 'space name')]); ?>

            <?php echo $form->field($model, 'description')->textarea(['placeholder' => Yii::t('SpaceModule.views_create_create', 'space description'), 'rows' => '3']); ?>

            <a data-toggle="collapse" id="access-settings-link" href="#collapse-access-settings"
               style="font-size: 11px;"><i
                    class="fa fa-caret-right"></i> <?php echo Yii::t('SpaceModule.views_create_create', 'Advanced access settings'); ?>
            </a>

            <div id="collapse-access-settings" class="panel-collapse collapse">
                <br/>

                <?php $joinPolicies = array(0 => Yii::t('SpaceModule.views_create_create', 'Only by invite'), 1 => Yii::t('SpaceModule.views_create_create', 'Invite and request'), 2 => Yii::t('SpaceModule.views_create_create', 'For everyone')); ?>

                <div class="row">
                    <div class="col-md-6">
                        <label for=""><strong><?php echo Yii::t('SpaceModule.views_create_create', 'Join Policy'); ?></strong></label>

                        <div class="chk_rdo">
                        <?php echo $form->field($model, 'join_policy')->radio(['value' => 0, 'id' => 'invite_radio', 'label' => Yii::t('SpaceModule.base', 'Only by invite')]); ?>
                        <?php echo $form->field($model, 'join_policy')->radio(['value' => 1, 'id' => 'request_radio', 'label' => Yii::t('SpaceModule.base', 'Invite and request')]); ?>
                        <?php echo $form->field($model, 'join_policy')->radio(['value' => 2, 'id' => 'everyone_radio', 'label' => Yii::t('SpaceModule.base', 'Everyone can enter')]); ?>
                        <br>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for=""><strong><?php echo Yii::t('SpaceModule.views_create_create', 'Visibility'); ?></strong></label>
                        <div class="chk_rdo">
                        <?php if (Yii::$app->user->permissionmanager->can(new CreatePublicSpace) && Yii::$app->user->permissionmanager->can(new CreatePrivateSpace())): ?>
                            <?php if (Setting::Get('allowGuestAccess', 'authentication_internal')) : ?>
                                <?php echo $form->field($model, 'visibility')->radio(['value' => 2, 'id' => 'public_radio_guests', 'label' => Yii::t('SpaceModule.base', 'Public (Members & Guests)')]); ?>
                            <?php endif; ?>

                            <?php echo $form->field($model, 'visibility')->radio(['value' => 1, 'id' => 'public_radio', 'label' => Yii::t('SpaceModule.base', 'Public (Members only)')]); ?>

                            <?php echo $form->field($model, 'visibility')->radio(['value' => 0, 'id' => 'private_radio', 'label' => Yii::t('SpaceModule.base', 'Private (Invisible)')]); ?>

                        <?php elseif (Yii::$app->user->permissionmanager->can(new CreatePublicSpace)): ?>
                            <?php echo $form->field($model, 'visibility')->radio(['value' => 0, 'id' => 'private_radio', 'label' => Yii::t('SpaceModule.base', 'Private (Invisible)')]); ?>
                        <?php elseif (Yii::$app->user->permissionmanager->can(new CreatePrivateSpace())): ?>
                            <?php echo $form->field($model, 'visibility')->radio(['value' => 0, 'id' => 'private_radio', 'label' => Yii::t('SpaceModule.base', 'Private (Invisible)')]); ?>
                        <?php endif; ?>
                            </div>
                    </div>
                </div>
            </div>
        </div>

        <div class=" modal-footer">
            <hr/>
            <br/>
            <?php
            echo \humhub\widgets\AjaxButton::widget([
                'label' => Yii::t('SpaceModule.views_create_create', 'Create'),
                'ajaxOptions' => [
                    'type' => 'POST',
                    'beforeSend' => new yii\web\JsExpression('function(){ setModalLoader(); }'),
                    'success' => new yii\web\JsExpression('function(html){ $("#globalModal").html(html); }'),
                    'url' => Url::to(['/space/create/create']),
                ],
                'htmlOptions' => [
                    'class' => 'btn btn-primary',
                    'id' => 'space-create-submit-button',
                ]
            ]);
            ?>

            <?php echo \humhub\widgets\LoaderWidget::widget(['id' => 'create-loader', 'cssClass' => 'loader-modal hidden']); ?>
        </div>

        <?php ActiveForm::end(); ?>
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
    <?php if ($model->hasErrors()) { ?>
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