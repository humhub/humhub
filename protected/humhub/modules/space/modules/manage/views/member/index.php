<?php

use yii\helpers\Html;
use humhub\widgets\GridView;
use humhub\widgets\ActiveForm;
use humhub\modules\space\models\Space;
use humhub\modules\space\modules\manage\widgets\MemberMenu;
use humhub\modules\user\grid\ImageColumn;
use humhub\modules\user\grid\DisplayNameColumn;
use humhub\modules\space\modules\manage\models\MembershipSearch;
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <?php echo Yii::t('SpaceModule.views_admin_members', '<strong>Manage</strong> members'); ?>
    </div>
    <?= MemberMenu::widget(['space' => $space]); ?>
    <div class="panel-body">

        <?php $form = ActiveForm::begin(['method' => 'get']); ?>
        <div class="row">
            <div class="col-md-8">
                <div class="input-group">
                    <?= Html::activeTextInput($searchModel, 'freeText', ['class' => 'form-control', 'placeholder' => Yii::t('AdminModule.user', 'Search by name, email or id.')]); ?>
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button>
                    </span>
                </div>     
            </div>
            <div class="col-md-4">
                <?= Html::activeDropDownList($searchModel, 'group_id', MembershipSearch::getRoles($space), ['class' => 'form-control', 'onchange' => 'this.form.submit()']); ?>
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
                        function ($data) use (&$groups) {
                            if ($data->created_at == '') {
                                return Yii::t('SpaceModule.views_admin_members', '-');
                            }

                            return humhub\widgets\TimeAgo::widget(['timestamp' => $data->last_visit]);
                        }
                    ],
                    [
                        'attribute' => 'last_visit',
                        'format' => 'raw',
                        'value' =>
                        function ($data) use (&$groups) {
                            if ($data->last_visit == '') {
                                return Yii::t('SpaceModule.views_admin_members', 'never');
                            }

                            return humhub\widgets\TimeAgo::widget(['timestamp' => $data->last_visit]);
                        }
                    ],
                    [
                        'label' => Yii::t('SpaceModule.views_admin_members', 'Role'),
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
                            'view' => function($url, $model) {
                                return false;
                            },
                            'update' => function($url, $model) {
                                return false;
                            },
                            'delete' => function($url, $model) {
                                return Html::a('<i class="fa fa-times"></i>', '#', [
                                            'title' => Yii::t('SpaceModule.manage', 'Remove from space'),
                                            'class' => 'btn btn-danger btn-xs tt',
                                            'data-confirm' => 'Are you really sure?'
                                ]);
                            }
                        ],
                    ],
                ],
            ]);
            ?>
        </div>
    </div>
</div>
