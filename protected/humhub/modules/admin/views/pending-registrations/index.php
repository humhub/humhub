<?php

use humhub\modules\admin\widgets\ExportButton;
use humhub\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var $searchModel \humhub\modules\admin\models\PendingRegistrationSearch */
?>

<div class="panel-body">

    <h4><?= Yii::t('AdminModule.base', 'Pending user registrations') ?></h4>

    <div class="help-block">
        <?= Yii::t(
            'AdminModule.views_approval_index',
            'The following list contains all pending sign-ups and invites.'
        ) ?>
    </div>

    <div class="pull-right">
        <?= humhub\libs\Html::backButton(
            ['/admin/user/index'],
            ['label' => Yii::t('AdminModule.base', 'Back to user overview')]
        ) ?>
        <?= ExportButton::widget(['filter' => 'PendingRegistrationSearch']) ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'email',
            'originator.username',
            'language',
            'created_at',
            [
                'attribute' => 'source',
                'filter' => \yii\helpers\Html::activeDropDownList($searchModel, 'source', $types),
                'options' => ['width' => '40px'],
                'format' => 'raw',
                'value' => function ($data) use ($types) {
                    return isset($types[$data->source]) ?: Html::encode($data->source);
                },
            ],
            [
                'header' => Yii::t('AdminModule.views_user_index', 'Actions'),
                'class' => 'yii\grid\ActionColumn',
                'template' => '{resend} {delete}',
                'buttons' => [
                    'resend' => function ($url, $model, $key) {
                        return Html::a(
                            '<i class="fa fa-envelope"></i>',
                            Url::to(['resend', 'id' => $model->id]),
                            ['class' => 'btn btn-primary btn-xs tt']
                        );
                    },
                    'delete' => function ($url, $model, $key) {
                        return Html::a(
                            '<i class="fa fa-trash"></i>',
                            Url::to(['delete', 'id' => $model->id]),
                            ['class' => 'btn btn-primary btn-xs tt']
                        );
                    },
                ],
            ],

        ]
    ]) ?>
</div>