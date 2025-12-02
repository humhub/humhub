<?php

use humhub\helpers\Html;
use humhub\modules\admin\assets\AdminGroupAsset;
use humhub\modules\admin\models\forms\AddGroupMemberForm;
use humhub\modules\admin\models\UserSearch;
use humhub\modules\user\grid\DisplayNameColumn;
use humhub\modules\user\grid\ImageColumn;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\UserPickerField;
use humhub\widgets\bootstrap\Alert;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\GridView;
use yii\helpers\Url;

/* @var $group Group */
/* @var $addGroupMemberForm AddGroupMemberForm */
/* @var $searchModel UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $canManage bool */
/* @var $canModifyMembers bool */

AdminGroupAsset::register($this);
?>

<?php $this->beginContent('@admin/views/group/_manageLayout.php', ['group' => $group]) ?>
<div class="panel-body">
    <?php if ($subGroupUsersCount = $group->getSubGroupUsersCount()) : ?>
        <?= Alert::accent(Yii::t('AdminModule.user', 'This user group includes {count} additional members from the subgroup(s). To view the subgroups, check the Settings section.', [
                '{count}' => '<strong>' . $subGroupUsersCount . '</strong>',
            ]))->closeButton(false) ?>
    <?php endif ?>

    <div class="row">
        <?php if ($canModifyMembers) : ?>
        <div class="col-lg-6">
            <?php $form = ActiveForm::begin(['action' => ['/admin/group/add-members']]); ?>
            <div class="select2-humhub-append input-group flex-nowrap">
                <?= UserPickerField::widget([
                    'model' => $addGroupMemberForm,
                    'attribute' => 'userGuids',
                    'url' => Url::to(['/admin/group/new-member-search', 'id' => $group->id]),
                    'placeholder' => Yii::t('AdminModule.user', 'Add new members...'),
                    'focus' => true,
                ]) ?>
                <?= Html::activeHiddenInput($addGroupMemberForm, 'groupId', ['value' => $group->id]) ?>
                <?= Button::primary()->submit()->icon('add') ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
        <?php endif; ?>
        <div class="<?= $canModifyMembers ? 'col-lg-6' : 'col-lg-12' ?>">
            <?php $form = ActiveForm::begin(['method' => 'get']); ?>
            <div class="input-group">
                <?= Html::activeTextInput($searchModel, 'freeText', ['class' => 'form-control', 'placeholder' => Yii::t('AdminModule.user', 'Search by name, email or id.')]); ?>
                <button class="btn btn-light" type="submit"><i class="fa fa-search"></i></button>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <div class="table-responsive">
        <?= GridView::widget(
            [
                'dataProvider' => $dataProvider,
                #'filterModel' => $searchModel,
                'summary' => '',
                'columns' => [
                    ['class' => ImageColumn::class],
                    ['class' => DisplayNameColumn::class],
                    [
                        'attribute' => 'created_at',
                        'label' => Yii::t('AdminModule.user', 'Member since'),
                        'format' => 'datetime',
                        'options' => ['style' => 'width:160px; min-width:160px;'],
                    ],
                    [
                        'attribute' => 'is_manager',
                        'label' => Yii::t('AdminModule.user', 'Group Manager'),
                        'format' => 'raw',
                        'value' => fn(User $user) => Html::dropDownList('role', $group->isManager($user), [
                            Yii::t('AdminModule.user', 'No'),
                            Yii::t('AdminModule.user', 'Yes'),
                        ], [
                            'data-action-change' => 'admin.group.setManagerRole',
                            'data-action-url' => Url::to(['edit-manager-role']),
                            'data-userid' => $user->id,
                            'data-groupid' => $group->id,
                            'disabled' => !$canManage,
                        ]),
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'visible' => $canModifyMembers,
                        'options' => ['style' => 'width:40px; min-width:40px;'],
                        'template' => '{delete}',
                        'buttons' => [
                            'delete' => fn($url, $model) => $model->getGroups()->count() > 1
                                ? Button::danger()
                                    ->tooltip(Yii::t('AdminModule.user', 'Remove from group'))
                                    ->action('admin.group.removeMember', Url::to(['remove-group-user', 'id' => $group->id, 'userId' => $model->id]))
                                    ->icon('remove')
                                    ->sm()
                                    ->confirm()
                                : Button::danger()
                                    ->tooltip(Yii::t('AdminModule.user', 'The user cannot be removed from this Group, as users are required to be assigned to at least one Group.'))
                                    ->icon('remove')
                                    ->options(['disabled' => true])
                                    ->sm()
                                    ->loader(false),
                        ],
                    ],
                ],
            ]
        ) ?>
    </div>
</div>
<?php $this->endContent(); ?>
