<?php
/* @var $this \humhub\modules\ui\view\components\View */
/* @var $canInviteExternal bool */
/* @var $canAddWithoutInvite bool */
/* @var $submitText string */
/* @var $submitAction string */
/* @var $model \humhub\modules\space\models\forms\InviteForm */
/* @var $attribute string */

/* @var $searchUrl string */

use humhub\modules\user\widgets\UserPickerField;
use humhub\widgets\ModalDialog;
use yii\bootstrap\ActiveForm;
use humhub\libs\Html;

$modal = ModalDialog::begin([
    'header' => Yii::t('SpaceModule.base', '<strong>Invite</strong> members'),
]);

$modalAnimationClass = ($model->hasErrors()) ? 'shake' : 'fadeIn';

if ($canInviteExternal && $model->hasErrors('inviteExternal')) {
    $isInviteExternalTabActiveClass = 'active';
    $isInviteTabActiveClass = '';
} else {
    $isInviteExternalTabActiveClass = '';
    $isInviteTabActiveClass = 'active';
}

$form = ActiveForm::begin([
    'id' => 'space-invite-modal-form',
    'action' => $submitAction,
]);
?>
<div class="modal-body">
    <?php if ($canInviteExternal) : ?>
        <div class="text-center">
            <ul id="tabs" class="nav nav-tabs tabs-center" data-tabs="tabs">
                <li class="<?= $isInviteTabActiveClass ?> tab-internal">
                    <a href="#internal" data-toggle="tab">
                        <?= Yii::t('SpaceModule.base', 'Pick users'); ?>
                    </a>
                </li>
                <li class="<?= $isInviteExternalTabActiveClass ?> tab-external">
                    <a href="#external" data-toggle="tab">
                        <?= Yii::t('SpaceModule.base', 'Invite by email'); ?>
                    </a>
                </li>
            </ul>
        </div>
        <br/>
    <?php endif; ?>

    <div class="tab-content">
        <div class="tab-pane <?= $isInviteTabActiveClass ?>" id="internal">

            <?= Yii::t('SpaceModule.base',
                'To invite users to this space, please type their names below to find and pick them.'); ?>

            <br><br>

            <?= $form->field($model, 'invite')
                ->widget(UserPickerField::class, ['disabledItems' => [Yii::$app->user->guid], 'url' => $searchUrl, 'focus' => true, 'id' => 'space-invite-user-picker']); ?>

            <?php if ($canAddWithoutInvite) : ?>
                <br/>
                <?= $form
                ->field($model, 'withoutInvite')
                ->label(Yii::t('SpaceModule.base',
                    'Add users without invitation'))
                ->checkbox() ?>
                <br/>

                <?= $form
                    ->field($model, 'allRegisteredUsers')
                    ->label(Yii::t('SpaceModule.base',
                        'Select all registered users'))
                    ->checkbox() ?>
            <?php endif; ?>

        </div>

        <?php if ($canInviteExternal) : ?>
            <div class="<?= $isInviteExternalTabActiveClass ?> tab-pane" id="external">
                <?= Yii::t('SpaceModule.base',
                    'You can also invite external users, which are not registered now. Just add their e-mail addresses separated by comma.'); ?>
                <br><br>
                <?= $form->field($model, 'inviteExternal')->textarea([
                    'id' => 'space-invite-external',
                    'rows' => '3',
                    'placeholder' => Yii::t('SpaceModule.base', 'Email addresses'),
                ]); ?>
            </div>
        <?php endif; ?>

        <script <?= Html::nonce() ?>>
            $('#inviteform-allregisteredusers').on('change', function () {
                var userPicker = humhub.modules.action.Component.instance('#space-invite-user-picker');

                if ($(this).is(':checked')) {
                    userPicker.clear();
                    userPicker.disable();
                } else {
                    userPicker.disable(false);
                }
            });
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
    <a href="#" data-action-click="ui.modal.submit" data-action-submit class="btn btn-primary"
       data-ui-loader><?= $submitText ?></a>
</div>
<?php ActiveForm::end() ?>

<?php ModalDialog::end(); ?>
