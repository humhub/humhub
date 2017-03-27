<?php

use yii\helpers\Url;
use yii\helpers\Html;
use humhub\widgets\GridView;
?>

<div class="panel-body">
    <h4><?= Yii::t('AdminModule.user', 'Overview'); ?></h4>
    <div class="help-block">
        <?= Yii::t('AdminModule.views_user_index', 'This overview contains a list of each registered user with actions to view, edit and delete users.'); ?>
    </div>
    <div class="table-responsive">
        <div class="pull-right">
            <?= Html::a('<i class="fa fa-plus" aria-hidden="true"></i>&nbsp;&nbsp;' . Yii::t('AdminModule.views_user_index', 'Add new user'), ['/admin/user/add'], ['class' => 'btn btn-success', 'data-ui-loader'=>'']); ?>
            <?= Html::a('<i class="fa fa-paper-plane" aria-hidden="true"></i>&nbsp;&nbsp;' . Yii::t('AdminModule.views_user_index', 'Send invite'), ['/user/invite'], ['class' => 'btn btn-success', 'data-target' => '#globalModal']); ?>
        </div>

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
                    'attribute' => 'last_login',
                    'label' => Yii::t('AdminModule.views_user_index', 'Last login'),
                    'filter' => \yii\jui\DatePicker::widget([
                        'model' => $searchModel,
                        'attribute' => 'last_login',
                        'options' => ['style' => 'width:86px;', 'class' => 'form-control'],
                    ]),
                    'value' => function ($data) {
                        return ($data->last_login == NULL) ? Yii::t('AdminModule.views_user_index', 'never') : Yii::$app->formatter->asDate($data->last_login);
                    }
                ],
                [
                    'header' => Yii::t('AdminModule.views_user_index', 'Actions'),
                    'class' => 'yii\grid\ActionColumn',
                    'options' => ['style' => 'width:80px; min-width:80px;'],
                    'buttons' => [
                        'view' => function($url, $model) {
                            return Html::a('<i class="fa fa-eye"></i>', $model->getUrl(), ['class' => 'btn btn-primary btn-xs tt']);
                        },
                        'update' => function($url, $model) {
                            return Html::a('<i class="fa fa-pencil"></i>', Url::toRoute(['edit', 'id' => $model->id]), ['class' => 'btn btn-primary btn-xs tt']);
                        },
                        'delete' => function($url, $model) {
                            return Html::a('<i class="fa fa-times"></i>', Url::toRoute(['delete', 'id' => $model->id]), ['class' => 'btn btn-danger btn-xs tt']);
                        }
                    ],
                ],
            ],
        ]);
        ?>
    </div>
</div>