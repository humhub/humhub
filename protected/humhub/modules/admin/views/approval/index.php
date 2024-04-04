<?php

use humhub\libs\Html;
use humhub\modules\admin\controllers\ApprovalController;
use humhub\modules\admin\grid\ApprovalActionColumn;
use humhub\modules\admin\models\forms\ApproveUserForm;
use humhub\modules\admin\models\UserApprovalSearch;
use humhub\modules\user\grid\DisplayNameColumn;
use humhub\modules\user\grid\ImageColumn;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\User;
use humhub\widgets\Button;
use humhub\widgets\GridView;
use yii\data\ActiveDataProvider;

/** @var $searchModel UserApprovalSearch */
/** @var $dataProvider ActiveDataProvider */
/** @var $availableProfileFields ProfileField[] */
/** @var $profileFieldsColumns ProfileField[] */

$columns = [
    [
        'header' => Html::checkbox('select-all'),
        'format' => 'raw',
        'value' => static function (User $model) {
            return Html::checkbox('ids[]', false, ['id' => 'user-select-' . $model->id, 'value' => $model->id]);
        }
    ],
    ['class' => ImageColumn::class],
    ['class' => DisplayNameColumn::class],
    'email',
    'originator.username',
];
foreach ($profileFieldsColumns as $profileField) {
    $columns[] = [
        'attribute' => 'profile.' . $profileField->internal_name,
        'value' => static function (User $model) use ($profileField) {
            return $profileField->getUserValue($model);
        }
    ];
}
$columns[] = 'created_at';
$columns[] = [
    'class' => ApprovalActionColumn::class,
    'options' => ['style' => 'width:160px;'],
    'buttons' => [
        'view' => function ($url, $model) {
            return Button::defaultType()->link(['/admin/user/edit', 'id' => $model->id])->icon('edit')->sm()->tooltip(Yii::t('AdminModule.user', 'Edit'));
        },
        'sendMessage' => function ($url, $model) {
            $nbMsgSent = ApproveUserForm::getNumberMessageSent($model->id);
            return
                Button::primary($nbMsgSent ?: '')->link(['send-message', 'id' => $model->id])->icon('paper-plane')->sm()->tooltip(
                    Yii::t('AdminModule.user', 'Send a message') .
                    ($nbMsgSent ? ' (' . Yii::t('AdminModule.user', '{nbMsgSent} already sent', ['nbMsgSent' => $nbMsgSent]) . ')' : '')
                );
        },
        'update' => function ($url, $model) {
            return Button::success()->link(['approve', 'id' => $model->id])->icon('check')->sm()->tooltip(Yii::t('AdminModule.user', 'Approve'));
        },
        'delete' => function ($url, $model) {
            return Button::danger()->link(['decline', 'id' => $model->id])->icon('times')->sm()->tooltip(Yii::t('AdminModule.user', 'Decline'));
        },
    ],
];
?>

<div class="panel-body">

    <div class="dropdown pull-right">
        <?php if (!empty($availableProfileFields)): ?>
            <?= Button::defaultType()
                ->icon('cog')
                ->loader(false)
                ->options(['data-toggle' => 'dropdown']) ?>
        <?php endif; ?>

        <?= Html::beginForm('#', 'post', [
            'id' => 'screen-options',
            'class' => 'dropdown-menu p-4'
        ]) ?>
        <h6 class="dropdown-header">
            <strong><?= Yii::t('AdminModule.user', 'Select the profile fields you want to add as columns') ?></strong>
        </h6>
        <li class="divider"></li>
        <div style="padding: 0 15px;">
            <?php foreach ($availableProfileFields as $field): ?>
                <?= Html::checkbox('screenProfileFieldsId[]', array_key_exists($field->id, $profileFieldsColumns), ['id' => 'profile-select-' . $field->id, 'value' => $field->id, 'label' => Yii::t($field->getTranslationCategory(), $field->title)]) ?>
            <?php endforeach; ?>
            <br>
            <?= Html::saveButton(Yii::t('AdminModule.user', 'Apply')) ?>
        </div>
        <?= Html::endForm() ?>
    </div>

    <h4><?= Yii::t('AdminModule.user', 'Pending user approvals') ?></h4>

    <div class="help-block">
        <?= Yii::t('AdminModule.user', 'The following list contains all registered users awaiting an approval.') ?>
    </div>

    <?= Html::beginForm(['bulk-actions'], 'post', ['id' => 'admin-approval-form']) ?>

    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => $columns,
    ]);
    ?>

    <br>
    <?= Html::button(Yii::t('AdminModule.user', 'Email all selected'), [
        'class' => 'btn btn-primary btn-sm bulk-actions-button bulk-actions-button-email',
        'data-confirm' => Yii::t('AdminModule.user', 'Are you really sure? The selected users will be notified by e-mail.'),
    ]) ?>
    <?= Html::button(Yii::t('AdminModule.user', 'Approve all selected'), [
        'class' => 'btn btn-success btn-sm bulk-actions-button bulk-actions-button-approve',
        'data-confirm' => Yii::t('AdminModule.user', 'Are you really sure? The selected users will be approved and notified by e-mail.'),
    ]) ?>
    <?= Html::button(Yii::t('AdminModule.user', 'Decline all selected'), [
        'class' => 'btn btn-danger btn-sm bulk-actions-button bulk-actions-button-decline',
        'data-confirm' => Yii::t('AdminModule.user', 'Are you really sure? The selected users will be deleted and notified by e-mail.'),
    ]) ?>

    <?= Html::endForm() ?>
</div>

<script <?= Html::nonce() ?>>
    $(function () {

        $('.bulk-actions-button-email').on('click', function () {
            $('#admin-approval-form').find("input[name='action']").remove();
            $('#admin-approval-form').append('<input type="hidden" name="action" value="<?= ApprovalController::ACTION_SEND_MESSAGE ?>" />');
            //$('#admin-approval-form').submit();
        });
        $('.bulk-actions-button-approve').on('click', function () {
            $('#admin-approval-form').find("input[name='action']").remove();
            $('#admin-approval-form').append('<input type="hidden" name="action" value="<?= ApprovalController::ACTION_APPROVE ?>" />');
            //$('#admin-approval-form').submit();
        });
        $('.bulk-actions-button-decline').on('click', function () {
            $('#admin-approval-form').find("input[name='action']").remove();
            $('#admin-approval-form').append('<input type="hidden" name="action" value="<?= ApprovalController::ACTION_DECLINE ?>" />');
            //$('#admin-approval-form').submit();
        });

        const selectAllCheckbox = $('#admin-approval-form input[name="select-all"]');
        const usersCheckbox = $('#admin-approval-form input[name="ids[]"]');
        const bulkActionsButtons = $('.bulk-actions-button');

        // Enable bulk buttons only when at least one user is selected
        const updateButtonState = function () {
            bulkActionsButtons.attr('disabled', 'disabled');
            usersCheckbox.each(function () {
                if ($(this).is(":checked")) {
                    bulkActionsButtons.removeAttr('disabled');
                }
            });
        }

        updateButtonState();
        usersCheckbox.on('change', function () {
            updateButtonState();
        })

        // Check or uncheck all users with the header checkbox
        selectAllCheckbox.on('change', function () {
            usersCheckbox.prop('checked', $(this).is(":checked"));
            updateButtonState();
        })
    });
</script>
