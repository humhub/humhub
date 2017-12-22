<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use humhub\widgets\GridView;

\humhub\modules\admin\assets\AdminGroupAsset::register($this);

?>

<?php $this->beginContent('@admin/views/group/_manageLayout.php', ['group' => $group]) ?>
<div class="panel-body">
    <?php $form = ActiveForm::begin(['action' => ['/admin/group/add-members']]); ?>
    <div class="form-group">
        <div class="input-group select2-humhub-append">
            <?=
            humhub\modules\user\widgets\UserPickerField::widget([
                'model' => $addGroupMemberForm,
                'attribute' => 'userGuids',
                'url' => Url::to(['/admin/group/new-member-search', 'id' => $group->id]),
                'placeholder' => Yii::t('AdminModule.views_group_manageGroupUser', 'Add new members...'),
                'focus' => true,
            ])
            ?>
            <?= Html::activeHiddenInput($addGroupMemberForm, 'groupId', ['value' => $group->id]) ?>
            <span class="input-group-btn">
                <button type="submit" class="btn btn-primary" style="height:40px;" data-ui-loader><i class="fa fa-plus"></i></button>
            </span>
        </div>
    </div>
        <?php ActiveForm::end(); ?>

    <div class="table-responsive">
        <?php
            $actionUrl = Url::to(['edit-manager-role']);
            echo GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    [
                        'attribute' => 'id',
                        'options' => ['style' => 'width:40px;'],
                        'format' => 'raw',
                        'value' => function($data) {
                            return $data->id;
                        },
                    ],
                    'username',
                    'email',
                    'profile.firstname',
                    'profile.lastname',
                    [
                        'attribute' => 'is_manager',
                        'visible' => $isManagerApprovalSetting,
                        'label' => Yii::t('AdminModule.views_user_index', 'Group Manager'),
                        'format' => 'raw',
                        'value' => function ($data) use ($group, $actionUrl) {
                            $isManager = $group->isManager($data);
                            $yesSelected = ($isManager) ? 'selected' : '';
                            $noSelected = ($isManager) ? '' : 'selected';
                            $result = '<select class="editableCell form-control" data-action-change="admin.group.setManagerRole" data-action-url="'.$actionUrl.'" data-userid="' . $data->id . '"  data-groupid="' . $group->id . '">';
                            $result .= '<option value="0" ' . $noSelected . '>' . Yii::t('AdminModule.views_group_manageGroupUser', 'No') . '</option>';
                            $result .= '<option value="1" ' . $yesSelected . '>' . Yii::t('AdminModule.views_group_manageGroupUser', 'Yes') . '</option>';
                            return $result;
                        }
                    ],
                    [
                        'header' => Yii::t('AdminModule.views_user_index', 'Actions'),
                        'class' => 'yii\grid\ActionColumn',
                        'options' => ['style' => 'width:80px; min-width:80px;'],
                        'buttons' => [
                            'view' => function($url, $model) {
                                return false;
                            },
                            'update' => function($url, $model) use ($group) {
                                return false;
                            },
                            'delete' => function($url, $model) use ($group) {
                                return Html::a('<i class="fa fa-times"></i>', '#', [
                                            'data-action-click' => 'admin.group.removeMember',
                                            'data-action-url' => Url::to(['remove-group-user', 'id' => $group->id, 'userId' => $model->id]),
                                            'title' => Yii::t('AdminModule.views_group_manageGroupUser', 'Remove from group'),
                                            'class' => 'btn btn-danger btn-xs tt']);
                            }
                        ],
                    ],
                ],
            ]
        );?>
    </div>
</div>
<?php $this->endContent(); ?>