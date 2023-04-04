<?php
/* @var $this \humhub\modules\ui\view\components\View */
/* @var $canInviteByEmail bool */
/* @var $canInviteByLink bool */
/* @var $canAddWithoutInvite bool */
/* @var $submitText string */
/* @var $submitAction string */
/* @var $model \humhub\modules\space\models\forms\InviteForm */
/* @var $attribute string */

/* @var $searchUrl string */

use humhub\modules\user\widgets\UserPickerField;
use humhub\widgets\Button;
use humhub\widgets\ModalButton;
use humhub\widgets\ModalDialog;
use yii\bootstrap\ActiveForm;
use humhub\libs\Html;

$modal = ModalDialog::begin([
    'header' => Yii::t('SpaceModule.base', '<strong>Invite</strong> members'),
]);

$modalAnimationClass = ($model->hasErrors()) ? 'shake' : 'fadeIn';

if ($canInviteByEmail && $model->hasErrors('inviteEmails')) {
    $isInviteByEmailTabActiveClass = 'active';
    $isInviteTabActiveClass = '';
} else {
    $isInviteByEmailTabActiveClass = '';
    $isInviteTabActiveClass = 'active';
}

$form = ActiveForm::begin([
    'id' => 'space-invite-modal-form',
    'action' => $submitAction,
]);
?>
<div class="modal-body">
    <?php if ($canInviteByEmail || $canInviteByLink) : ?>
        <div class="text-center">
            <ul id="tabs" class="nav nav-tabs tabs-center" data-tabs="tabs">
                <li class="<?= $isInviteTabActiveClass ?> tab-user-picker">
                    <a href="#user-picker" data-toggle="tab">
                        <?= Yii::t('SpaceModule.base', 'Pick users'); ?>
                    </a>
                </li>
                <?php if ($canInviteByEmail) : ?>
                    <li class="<?= $isInviteByEmailTabActiveClass ?> tab-invite-by-email">
                        <a href="#invite-by-email" data-toggle="tab">
                            <?= Yii::t('SpaceModule.base', 'Invite by email'); ?>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if ($canInviteByLink) : ?>
                    <li class="tab-invite-by-link">
                        <a href="#invite-by-link" data-toggle="tab">
                            <?= Yii::t('SpaceModule.base', 'Invite by link'); ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        <br/>
    <?php endif; ?>

    <div class="tab-content">
        <div class="tab-pane <?= $isInviteTabActiveClass ?>" id="user-picker">

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

        <?php if ($canInviteByEmail) : ?>
            <div class="<?= $isInviteByEmailTabActiveClass ?> tab-pane" id="invite-by-email">
                <?= Yii::t('SpaceModule.base',
                    'You can also invite external users by email, which are not registered now. Just add their e-mail addresses separated by comma.'); ?>
                <br><br>
                <?= $form->field($model, 'inviteEmails')->textarea([
                    'id' => 'space-invite-by-email',
                    'rows' => '3',
                    'placeholder' => Yii::t('SpaceModule.base', 'Email addresses'),
                ]); ?>
            </div>
        <?php endif; ?>

        <?php if ($canInviteByLink) : ?>
            <div class="tab-pane" id="invite-by-link">
                <?= Yii::t('SpaceModule.base',
                    'You can invite external users who are currently not registered via link. All you need to do is share this secure link with them.'); ?>
                <br><br>

                <div><strong><?= Yii::t('SpaceModule.base',
                            'Invite link') ?></strong></div>
                <div class="input-group" style="width: 100%;">
                    <?= Html::textarea('secureLink', $model->getInviteLink(), ['readonly' => 'readonly', 'class' => 'form-control']) ?>
                    <?php if (Yii::$app->controller->id === 'membership' && $model->space->isAdmin()) : ?>
                        <a href="#" class="pull-right"
                           data-action-confirm-header="<?= Yii::t('SpaceModule.base', 'Create new link') ?>",
                           data-action-confirm="<?= Yii::t('SpaceModule.base', 'Please note that any links you have previously created will become invalid as soon as you create a new one. Would you like to proceed?') ?>"
                           data-action-click="ui.modal.load"
                           data-action-click-url="<?= $model->space->createUrl('/space/membership/reset-invite-link') ?>">
                            <small><?= Yii::t('SpaceModule.base', 'Create new link'); ?></small>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>
<div class="modal-footer">
    <a id="space-invite-submit-btn" href="#" data-action-click="ui.modal.submit" data-action-submit
       class="btn btn-primary"
       data-ui-loader><?= $submitText ?></a>
    <?= Button::primary(Yii::t('SpaceModule.base',
        'Send the link via email'))
        ->link('mailto:' .
            '?subject=' . rawurlencode(Yii::t('UserModule.base', 'You\'ve been invited to join {space} on {appName}', ['space' => $model->space->name, 'appName' => Yii::$app->name])) .
            '&body=' . rawurlencode($this->renderFile($this->findViewFile('@humhub/modules/user/views/mails/plaintext/UserInviteSpace'), [
                'originator' => Yii::$app->user->identity,
                'space' => $model->space,
                'registrationUrl' => $model->getInviteLink()
            ])))
        ->style(['display' => 'none'])
        ->id('space-invite-send-link-by-email-btn')
        ->icon('paper-plane')
        ->loader(false)
    ?>
</div>
<?php ActiveForm::end() ?>

<?php ModalDialog::end(); ?>

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

    $('.tab-user-picker a').on('shown.bs.tab', function (e) {
        $('#space-invite-user-picker').data('picker').focus();
        $('#space-invite-submit-btn').show();
        $('#space-invite-send-link-by-email-btn').hide();
    });

    $('.tab-invite-by-email a').on('shown.bs.tab', function (e) {
        $('#space-invite-by-email').focus();
        $('#space-invite-submit-btn').show();
        $('#space-invite-send-link-by-email-btn').hide();
    });

    $('.tab-invite-by-link a').on('shown.bs.tab', function (e) {
        $('#space-invite-by-link').focus();
        $('#space-invite-submit-btn').hide();
        $('#space-invite-send-link-by-email-btn').show();
    });

    $(function () {
        $('textarea[name="secureLink"]').click(function () {
            $(this).select();
            navigator.clipboard.writeText($(this).val());
            const successMsg = <?= json_encode(Yii::t('SpaceModule.base', 'The secure link has been copied in your clipboard!'), JSON_HEX_TAG) ?>;
            humhub.modules.ui.status.success(successMsg);
        });
    });
</script>
