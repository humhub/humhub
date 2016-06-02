<?php

use yii\helpers\Url;
use yii\helpers\Html;
use humhub\widgets\GridView;
?>

<div class="panel-body">
    <h4><?php echo Yii::t('AdminModule.views_approval_index', 'Pending user approvals'); ?></h4>

    <div class="help-block">
        <?php echo Yii::t('AdminModule.views_approval_index', 'The following list contains all registered users awaiting an approval.'); ?>
    </div>

    <?php
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'username',
            'email',
            'profile.firstname',
            'profile.lastname',
            'profile.lastname',
            [
                'header' => Yii::t('AdminModule.views_approval_index', 'Actions'),
                'class' => 'yii\grid\ActionColumn',
                'options' => ['width' => '150px'],
                'buttons' => [
                    'view' => function() {
                        return;
                    },
                    'delete' => function($url, $model) {
                        return Html::a('Decline', Url::toRoute(['decline', 'id' => $model->id]), ['class' => 'btn btn-danger btn-sm']);
                    },
                            'update' => function($url, $model) {
                        return Html::a('Approve', Url::toRoute(['approve', 'id' => $model->id]), ['class' => 'btn btn-primary btn-sm']);
                    },
                        ],
                    ],
                ],
            ]);
            ?>
</div>