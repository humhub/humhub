<?php

use yii\helpers\Url;
use yii\helpers\Html;
use humhub\widgets\GridView;
use humhub\modules\space\modules\manage\widgets\MemberMenu;
?>
<?= MemberMenu::widget(['space' => $space]); ?>
<br />
<div class="panel panel-default">
    <div class="panel-heading">
        <?php echo Yii::t('SpaceModule.views_admin_members', '<strong>Pending</strong> approvals'); ?>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
        <?php
        $groups = $space->getUserGroups();


        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                'user.username',
                'user.profile.firstname',
                'user.profile.lastname',
                'request_message',
                [
                    'header' => Yii::t('SpaceModule.views_admin_members', 'Actions'),
                    'class' => 'yii\grid\ActionColumn',
                    'buttons' => [
                        'view' => function() {
                            return;
                        },
                        'delete' => function($url, $model) use($space) {
                            return Html::a(Yii::t('SpaceModule.views_admin_members', 'Reject'), $space->createUrl('reject-applicant', ['userGuid' => $model->user->guid]), ['class' => 'btn btn-danger btn-sm', 'data-method' => 'POST']);
                        },
                                'update' => function($url, $model) use($space) {
                            return Html::a(Yii::t('SpaceModule.views_admin_members', 'Approve'), $space->createUrl('approve-applicant', ['userGuid' => $model->user->guid]), ['class' => 'btn btn-primary btn-sm', 'data-method' => 'POST']);
                        },
                            ],
                        ],
                    ],
                ]);
                ?>
        </div>
    </div>
</div>
