<?php

use humhub\helpers\Html;
use humhub\modules\space\models\Space;
use humhub\modules\space\modules\manage\models\MembershipSearch;
use humhub\modules\space\modules\manage\widgets\MemberMenu;
use humhub\modules\user\grid\DisplayNameColumn;
use humhub\modules\user\grid\ImageColumn;
use humhub\widgets\GridView;
use yii\data\ActiveDataProvider;

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
                'request_message',
                [
                    'header' => Yii::t('SpaceModule.manage', 'Actions'),
                    'class' => 'yii\grid\ActionColumn',
                    'buttons' => [
                        'view' => fn() => '',
                        'delete' => fn($url, $model) => Html::a(Yii::t('SpaceModule.base', 'Decline'), $space->createUrl(
                            'reject-applicant',
                            ['userGuid' => $model->user->guid]
                        ), ['class' => 'btn btn-danger btn-sm', 'data-method' => 'POST']),
                        'update' => fn($url, $model) => Html::a(Yii::t('SpaceModule.base', 'Accept'), $space->createUrl(
                            'approve-applicant',
                            ['userGuid' => $model->user->guid]
                        ), ['class' => 'btn btn-primary btn-sm', 'data-method' => 'POST']),
                    ],
                ],
            ],
        ]);
        ?>
    </div>
</div>
