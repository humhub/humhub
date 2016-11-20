<?php

use yii\helpers\Url;
use yii\helpers\Html;
use humhub\widgets\GridView;
use yii\widgets\ActiveForm;
?>
<?php $this->beginContent('@admin/views/group/_manageLayout.php', ['group' => $group]) ?>
<div class="panel-body">
    <?php $form = ActiveForm::begin(['action' => ['/admin/group/add-members']]); ?>
    <div class="form-group">
        <div class="input-group select2-humhub-append">
            <?= humhub\modules\user\widgets\UserPickerField::widget([
                'model' => $addGroupMemberForm, 
                'attribute' => 'userGuids',
                'options' => ['data-placeholder' => Yii::t('AdminModule.views_group_manageGroupUser', 'Add new members...')]
                ])?>
            <?= Html::activeHiddenInput($addGroupMemberForm, 'groupId', ['value' => $group->id]) ?>
            <span class="input-group-btn">
                <button type="submit" class="btn btn-primary" style="height:40px;" data-ui-loader><i class="fa fa-plus"></i></button>
            </span>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

    <div class="table-responsive">
        <?php
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
                    'value' => function ($data) use ($group) {
                        $isManager = $group->isManager($data);
                        $yesSelected = ($isManager) ? 'selected' : '';
                        $noSelected = ($isManager) ? '' : 'selected';
                        $result = '<select class="managerDropDown editableCell form-control" data-userid="' . $data->id . '"  data-groupid="' . $group->id . '">';
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
                            return Html::a('<i class="fa fa-times"></i>', Url::toRoute(['remove-group-user', 'id' => $group->id, 'userId' => $model->id]), [
                                        'data-method' => 'POST',
                                        'title' => Yii::t('AdminModule.views_group_manageGroupUser', 'Remove from group'),
                                        'class' => 'btn btn-danger btn-xs tt']);
                        }
                            ],
                        ],
                    ],
                ]);
                ?>
            </div>
            <script type="text/javascript">
                $('.managerDropDown').on('change', function () {
                    var $this = $(this);
                    var userId = $this.data('userid');
                    var groupId = $this.data('groupid');
                    $.ajax("<?= Url::toRoute(['edit-manager-role']) ?>", {
                        method: 'POST',
                        data: {
                            'id': groupId,
                            'userId': userId,
                            'value': $this.val()
                        },
                        success: function () {
                            //success handler
                        }
                    });
                });
            </script>
        </div>
        <?php $this->endContent(); ?>