<?php

use yii\helpers\Url;
use yii\helpers\Html;
use humhub\widgets\GridView;
use humhub\modules\user\grid\ImageColumn;
use humhub\modules\user\grid\DisplayNameColumn;
?>

<div class="panel-body">
    <h4><?= Yii::t('AdminModule.views_approval_index', 'Pending user approvals'); ?></h4>

    <div class="help-block">
        <?= Yii::t('AdminModule.views_approval_index', 'The following list contains all registered users awaiting an approval.'); ?>
    </div>

    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => ImageColumn::class],
            ['class' => DisplayNameColumn::class],
            'email',
            'created_at',
            [
                'class' => 'yii\grid\ActionColumn',
                'options' => ['width' => '150px'],
                'buttons' => [
                    'view' => function() {
                        return;
                    },
                    'delete' => function($url, $model) {
                        return Html::a('Decline', Url::to(['decline', 'id' => $model->id]), ['class' => 'btn btn-danger btn-sm', 'data-ui-loader' => '']);
                    },
                    'update' => function($url, $model) {
                        return Html::a('Approve', Url::to(['approve', 'id' => $model->id]), ['class' => 'btn btn-primary btn-sm', 'data-ui-loader' => '']);
                    },
                ],
            ],
        ],
    ]);
    ?>
</div>
