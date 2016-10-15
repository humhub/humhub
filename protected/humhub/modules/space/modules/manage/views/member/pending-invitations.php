<?php


use yii\helpers\Html;
use humhub\widgets\GridView;
use humhub\modules\space\modules\manage\widgets\MemberMenu;
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <?php echo Yii::t('SpaceModule.views_admin_members', '<strong>Manage</strong> members'); ?>
    </div>
    <?= MemberMenu::widget(['space' => $space]); ?>
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
                    [
                        'attribute' => 'last_visit',
                        'format' => 'raw',
                        'value' =>
                        function($data) use(&$groups) {
                            return humhub\widgets\TimeAgo::widget(['timestamp' => $data->last_visit]);
                        }
                            ],
                            [
                                'header' => Yii::t('SpaceModule.views_admin_members', 'Actions'),
                                'class' => 'yii\grid\ActionColumn',
                                'buttons' => [
                                    'view' => function() {
                                        return;
                                    },
                                    'delete' => function($url, $model) use($space) {
                                        return Html::a('Cancel', $space->createUrl('remove', ['userGuid' => $model->user->guid]), ['class' => 'btn btn-danger btn-sm', 'data-confirm' => 'Are you sure?', 'data-method' => 'POST']);
                                    },
                                            'update' => function() {
                                        return;
                                    },
                                        ],
                                    ],
                                ],
                            ]);
                            ?>
        </div>
    </div>
</div>
