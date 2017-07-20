<?php

use yii\bootstrap\ActiveForm;
   
?>

<?php $modal = \humhub\widgets\ModalDialog::begin([
    'header' => Yii::t('SpaceModule.views_space_invite', '<strong>Invite</strong> members')
])?>


<?php
$modalAnimationClass = ($model->hasErrors()) ? 'shake' : 'fadeIn';

if ($canInviteExternal && $model->hasErrors('inviteExternal')) {
    $isInviteExternalTabActiveClass = 'active';
    $isInviteTabActiveClass = '';
} else {
    $isInviteExternalTabActiveClass = '';
    $isInviteTabActiveClass = 'active';
}
?>

<?php $form = ActiveForm::begin(['id' => 'space-invite-modal-form', 'action' => $submitAction]) ?>
    <div class="modal-body">
        <?php if ($canInviteExternal) : ?>
            <div class="text-center">
                <ul id="tabs" class="nav nav-tabs tabs-center" data-tabs="tabs">
                    <li class="<?= $isInviteTabActiveClass ?> tab-internal">
                        <a href="#internal" data-toggle="tab"><?= Yii::t('SpaceModule.views_space_invite', 'Pick users'); ?></a>
                    </li>
                    <li class="<?= $isInviteExternalTabActiveClass ?> tab-external">
                        <a href="#external" data-toggle="tab"><?= Yii::t('SpaceModule.views_space_invite', 'Invite by email'); ?></a>
                    </li>
                </ul>
            </div>
            <br/>
        <?php endif; ?>

        <div class="tab-content">
            <div class="tab-pane <?= $isInviteTabActiveClass ?>" id="internal">

                <?= Yii::t('SpaceModule.views_space_invite', 'To invite users to this space, please type their names below to find and pick them.'); ?>

                <br/><br/>
                <?= \humhub\modules\user\widgets\UserPickerField::widget([
                    'id' => 'space-invite-user-picker',
                    'form' => $form,
                    'model' => $model,
                    'attribute' => 'invite',
                    'url' => $searchUrl,
                    'disabledItems' => [Yii::$app->user->guid],
                    'focus' => true
                ])?>

            </div>

            <?php if ($canInviteExternal) : ?>
                <div class="<?= $isInviteExternalTabActiveClass ?> tab-pane" id="external">
                    <?= Yii::t('SpaceModule.views_space_invite', 'You can also invite external users, which are not registered now. Just add their e-mail addresses separated by comma.'); ?>
                    <br><br>
                    <?= $form->field($model, 'inviteExternal')->textarea(['id' => 'space-invite-external', 'rows' => '3', 'placeholder' => Yii::t('SpaceModule.views_space_invite', 'Email addresses')]); ?>
                </div>
            <?php endif; ?>
            <script>
            $('.tab-internal a').on('shown.bs.tab', function (e) {
                $('#space-invite-user-picker').data('picker').focus();
            });

            $('.tab-external a').on('shown.bs.tab', function (e) {
                $('#space-invite-external').focus();
            });
            </script>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#" data-action-click="ui.modal.submit" data-action-submit class="btn btn-primary"  data-ui-loader><?= $submitText ?></a>
    </div>
<?php ActiveForm::end() ?>
    
<?php \humhub\widgets\ModalDialog::end(); ?>