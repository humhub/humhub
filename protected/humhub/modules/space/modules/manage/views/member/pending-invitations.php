<?php

use humhub\modules\space\models\Space;
use humhub\modules\space\modules\manage\models\MembershipSearch;
use humhub\modules\user\grid\DisplayNameColumn;
use humhub\modules\user\grid\ImageColumn;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use humhub\widgets\GridView;
use humhub\modules\space\modules\manage\widgets\MemberMenu;
use humhub\widgets\TimeAgo;

/* @var $dataProvider ActiveDataProvider */
/* @var $searchModel MembershipSearch */
/* @var $space Space */
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <?= Yii::t('SpaceModule.manage', '<strong>Manage</strong> members'); ?>
    </div>
    <?= MemberMenu::widget(['space' => $space]); ?>
    <div class="panel-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-hover table-responsive'],
            'columns' => [
                ['class' => ImageColumn::class, 'userAttribute' => 'user'],
                ['class' => DisplayNameColumn::class, 'userAttribute' => 'user'],
                [
                    'attribute' => 'last_visit',
                    'format' => 'raw',
                    'value' => function ($data) use (&$groups) {
                        if (empty($model->last_visit)) {
                            return Yii::t('SpaceModule.manage', 'never');
                        }
                        return TimeAgo::widget(['timestamp' => $model->last_visit]);
                    }
                ],
                [
                    'label' => Yii::t('SpaceModule.manage', 'Invited By'),
                    'attribute' => 'originator',
                    'format' => 'raw',
                    'value' =>
                        function ($data) {
                            if (is_null($data->originator)) {
                                return Yii::t('SpaceModule.manage', '-');
                            }

                            return Html::a(HTML::encode($data->originator->getDisplayName()), $data->originator->getUrl());
                        }
                ],
                [
                    'header' => Yii::t('SpaceModule.manage', 'Actions'),
                    'class' => 'yii\grid\ActionColumn',
                    'buttons' => [
                        'view' => function () {
                            return '';
                        },
                        'delete' => function ($url, $model) use ($space) {
                            return Html::a(Yii::t('SpaceModule.base', 'Cancel'), $space->createUrl('remove', ['userGuid' => $model->user->guid]), ['class' => 'btn btn-danger btn-sm', 'data-confirm' => 'Are you sure?', 'data-method' => 'POST']);
                        },
                        'update' => function () {
                            return '';
                        },
                    ],
                ],
            ],
        ]);
        ?>
    </div>
</div>
