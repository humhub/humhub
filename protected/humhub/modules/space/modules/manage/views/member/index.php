<?php

use humhub\modules\space\modules\manage\widgets\MemberHeaderControlsMenu;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\widgets\Button;
use humhub\widgets\GridView;
use yii\bootstrap\ActiveForm;
use humhub\modules\space\models\Space;
use humhub\modules\space\modules\manage\widgets\MemberMenu;
use humhub\modules\user\grid\ImageColumn;
use humhub\modules\user\grid\DisplayNameColumn;
use humhub\modules\space\modules\manage\models\MembershipSearch;
use humhub\widgets\TimeAgo;
use yii\helpers\Html;

/* @var $space Space */
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <?= Yii::t('SpaceModule.manage', '<strong>Manage</strong> members'); ?>
    </div>
    <?= MemberMenu::widget(['space' => $space]); ?>
    <div class="panel-body">

        <?php $form = ActiveForm::begin(['method' => 'get']); ?>
        <div class="row">
            <div class="col-md-6">
                <div class="input-group">
                    <?= Html::activeTextInput($searchModel, 'freeText', ['class' => 'form-control', 'placeholder' => Yii::t('AdminModule.user', 'Search by name, email or id.')]); ?>
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button>
                    </span>
                </div>
            </div>
            <div class="col-md-4">
                <?= Html::activeDropDownList($searchModel, 'group_id', MembershipSearch::getRoles($space), ['class' => 'form-control', 'data-action-change' => 'ui.form.submit']); ?>
            </div>
            <div class="col-md-2">
                <div class="pull-right">
                    <?= MemberHeaderControlsMenu::widget([
                        'space' => $space,
                        'label' => Icon::get('cog'),
                    ]) ?>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
        <div class="table-responsive">

            <?php
            $groups = $space->getUserGroups();
            unset($groups[Space::USERGROUP_OWNER], $groups[Space::USERGROUP_GUEST], $groups[Space::USERGROUP_USER]);

            echo GridView::widget([
                'dataProvider' => $dataProvider,
                'summary' => '',
                'columns' => [
                    ['class' => ImageColumn::class, 'userAttribute' => 'user'],
                    ['class' => DisplayNameColumn::class, 'userAttribute' => 'user'],
                    [
                        'label' => 'Member since',
                        'attribute' => 'created_at',
                        'format' => 'raw',
                        'value' =>
                        function ($data) {
                            if ($data->created_at == '') {
                                return Yii::t('SpaceModule.manage', '-');
                            }

                            return TimeAgo::widget(['timestamp' => $data->created_at]);
                        }
                    ],
                    [
                        'attribute' => 'last_visit',
                        'format' => 'raw',
                        'value' =>
                        function ($data) use (&$groups) {
                            if (empty($data->last_visit)) {
                                return Yii::t('SpaceModule.manage', 'never');
                            }

                            return TimeAgo::widget(['timestamp' => $data->last_visit]);
                        }
                    ],
                    [
                        'label' => Yii::t('SpaceModule.manage', 'Role'),
                        'class' => 'humhub\libs\DropDownGridColumn',
                        'attribute' => 'group_id',
                        'submitAttributes' => ['user_id'],
                        'readonly' => function ($data) use ($space) {
                            if ($space->isSpaceOwner($data->user->id)) {
                                return true;
                            }
                            return false;
                        },
                        'filter' => $groups,
                        'dropDownOptions' => $groups,
                        'value' =>
                        function ($data) use (&$groups, $space) {
                            return $groups[$data->group_id];
                        }
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'options' => ['style' => 'width:40px; min-width:40px;'],
                        'buttons' => [
                            'view' => function ($url, $model) {
                                return false;
                            },
                            'update' => function ($url, $model) {
                                return false;
                            },
                            'delete' => function ($url, $model) use($space) {
                                return Button::danger()->tooltip(Yii::t('SpaceModule.manage', 'Remove from space'))
                                    ->link(['/space/manage/member/remove', 'userGuid' => $model->user->guid, 'container' => $space])
                                    ->options(['data-method' => 'POST', 'data-confirm' => Yii::t('SpaceModule.manage', 'Are you sure you want to remove this member.') ])
                                    ->icon('remove')->xs();
                            }
                        ],
                    ],
                ],
            ]);
            ?>
        </div>
    </div>
</div>
