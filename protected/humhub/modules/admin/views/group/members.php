<?php

use humhub\modules\admin\assets\AdminGroupAsset;
use humhub\modules\admin\models\forms\AddGroupMemberForm;
use humhub\modules\admin\models\UserSearch;
use humhub\modules\user\models\Group;
use humhub\widgets\Button;
use yii\helpers\Url;
use yii\helpers\Html;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\widgets\GridView;
use humhub\modules\user\grid\ImageColumn;
use humhub\modules\user\grid\DisplayNameColumn;
use humhub\modules\user\widgets\UserPickerField;

/* @var $group Group */
/* @var $addGroupMemberForm AddGroupMemberForm */
/* @var $searchModel UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $isManagerApprovalSetting boolean */

AdminGroupAsset::register($this);
?>

<?php $this->beginContent('@admin/views/group/_manageLayout.php', ['group' => $group]) ?>
<div class="panel-body">
     <div class="row">
        <div class="col-md-6">
            <?php $form = ActiveForm::begin(['action' => ['/admin/group/add-members']]); ?>
            <div class="input-group select2-humhub-append">
                <?= UserPickerField::widget([
                    'model' => $addGroupMemberForm,
                    'attribute' => 'userGuids',
                    'url' => Url::to(['/admin/group/new-member-search', 'id' => $group->id]),
                    'placeholder' => Yii::t('AdminModule.user', 'Add new members...'),
                    'focus' => true,
                ]) ?>
                <?= Html::activeHiddenInput($addGroupMemberForm, 'groupId', ['value' => $group->id]) ?>
                <span class="input-group-btn">
                    <?= Button::primary()->submit()->style('height:40px')->icon('add') ?>
                </span>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
        <div class="col-md-6">
            <?php $form = ActiveForm::begin(['method' => 'get']); ?>
            <div class="input-group">
                <?= Html::activeTextInput($searchModel, 'freeText', ['class' => 'form-control', 'placeholder' => Yii::t('AdminModule.user', 'Search by name, email or id.')]); ?>
                <span class="input-group-btn">
                    <button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button>
                </span>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <div class="table-responsive">
        <?php
        $actionUrl = Url::to(['edit-manager-role']);
        echo GridView::widget([
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
                    'visible' => $isManagerApprovalSetting,
                    'label' => Yii::t('AdminModule.user', 'Group Manager'),
                    'format' => 'raw',
                    'value' => function ($data) use ($group, $actionUrl) {
                        $isManager = $group->isManager($data);
                        $yesSelected = ($isManager) ? 'selected' : '';
                        $noSelected = ($isManager) ? '' : 'selected';
                        $result = '<select class="editableCell form-control" data-action-change="admin.group.setManagerRole" data-action-url="' . $actionUrl . '" data-userid="' . $data->id . '"  data-groupid="' . $group->id . '">';
                        $result .= '<option value="0" ' . $noSelected . '>' . Yii::t('AdminModule.user', 'No') . '</option>';
                        $result .= '<option value="1" ' . $yesSelected . '>' . Yii::t('AdminModule.user', 'Yes') . '</option>';
                        return $result;
                    }
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'options' => ['style' => 'width:40px; min-width:40px;'],
                    'buttons' => [
                        'view' => function($url, $model) {
                            return false;
                        },
                        'update' => function($url, $model) use ($group) {
                            return false;
                        },
                        'delete' => function($url, $model) use ($group) {
                            return Button::danger()
                                ->tooltip(Yii::t('AdminModule.user', 'Remove from group'))
                                ->action('admin.group.removeMember', Url::to(['remove-group-user', 'id' => $group->id, 'userId' => $model->id]))
                                ->icon('remove')->xs()
                                ->confirm();
                        }
                    ],
                ],
            ],
                ]
        );
        ?>
    </div>
</div>
<?php $this->endContent(); ?>
