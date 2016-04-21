<?php

use yii\helpers\Url;
use yii\helpers\Html;
use humhub\widgets\GridView;
use yii\widgets\ActiveForm;

?>
<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_group_manageGroupUser', '<strong>Manage</strong> group users'); ?></div>
    <div class="panel-body">
        <p>
            <?php echo Yii::t('AdminModule.views_group_manageGroupUser', 'In this view you can manage the users of group <b>{groupName}</b>', [
                'groupName' => $group->name
            ]); ?>
        </p>
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
                    'label' => Yii::t('AdminModule.views_user_index', 'Group Manager'),
                    'value' => function ($data) use ($group) {
                        return $group->isManager($data) ? Yii::t('AdminModule.views_group_manageGroupUser', 'Yes') : Yii::t('AdminModule.views_group_manageGroupUser', 'No');
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
                            if($group->isManager($model)) {
                                return Html::a('<i class="fa fa-asterisk"></i>', Url::toRoute(['toggle-admin', 'id' => $group->id, 'userId' => $model->id]), [
                                    'data-method' => 'POST',
                                    'title' => Yii::t('AdminModule.views_group_manageGroupUser', 'Remove admin role'),
                                    'class' => 'btn btn-danger btn-xs tt']);
                            } else {
                                return Html::a('<i class="fa fa-asterisk"></i>', Url::toRoute(['toggle-admin', 'id' => $group->id, 'userId' => $model->id], ['data-method' => 'POST']), [
                                    'data-method' => 'POST',
                                    'title' => Yii::t('AdminModule.views_group_manageGroupUser', 'Mark as admin'),
                                    'class' => 'btn btn-primary btn-xs tt']);
                            }
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
        <hr />
        <?php $form = ActiveForm::begin(['action' => ['/admin/group/add-members']]); ?>
            <label class="control-label" for="user_select"><?= Yii::t('AdminModule.views_group_manageGroupUser', 'Add Members') ?></label>
            <?php echo $form->field($addGroupMemberForm, 'userGuids', ['inputOptions' => ['id' => 'user_select']])->label(false); ?>
            <?php echo Html::activeHiddenInput($addGroupMemberForm, 'groupId', ['value' => $group->id]) ?>
            <?php
            echo \humhub\modules\user\widgets\UserPicker::widget([
                'inputId' => 'user_select',
                'model' => $addGroupMemberForm,
                'attribute' => 'userGuids',
                'userSearchUrl' => Url::toRoute('/admin/group/new-member-search'),
                'data' => ['id' => $group->id],
                'placeholderText' => Yii::t('AdminModule.views_group_manageGroupUser', 'Choose new members...')
            ]);
            ?>
            
            <?php echo Html::submitButton(Yii::t('AdminModule.views_group_manageGroupUser', 'Send'), ['class' => 'btn btn-primary']); ?>  
            <?php echo Html::a(Yii::t('AdminModule.views_group_manageGroupUser', 'Edit this group'), Url::toRoute(['edit', 'id' => $group->id]), ['class' => 'btn btn-danger']);?>
            
        <?php ActiveForm::end(); ?>
    </div>
</div>
