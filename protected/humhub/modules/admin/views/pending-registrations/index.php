<?php

use humhub\widgets\Button;
use humhub\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
?>

<div class="panel-body">
    <?= humhub\libs\Html::backButton(
        ['/admin/user/index'],
        ['label' => Yii::t('AdminModule.base', 'Back to user overview'), 'class' => 'pull-right']
    ); ?>
    <h4><?= Yii::t('AdminModule.base', 'Pending user registrations'); ?></h4>

    <div class="help-block">
        <?= Yii::t(
            'AdminModule.views_approval_index',
            'The following list contains all pending sign-ups and invites.'
        ); ?>
    </div>

    <div class="dropdown pull-right">
        <button class="btn btn-primary btn-sm " type="button" data-toggle="dropdown">
            <i class="fa fa-download"></i> <?= Yii::t('base', 'Export')?> <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
            <li><?= Button::asLink('csv', $urlExportCsv)->pjax(false)
                    ->icon('fa-file-code-o')->sm() ?></li>
            <li><?= Button::asLink('xlsx', $urlExportXlsx)->pjax(false)
                    ->icon('fa-file-excel-o')->sm() ?></li>
        </ul>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' =>
            [
                'email',
                'originator.username',
                'language',
                'created_at',
                [
                    'attribute' => 'source',
                    'filter' => Html::activeDropDownList($searchModel, 'source', array_merge(['' => ''], $types)),
                    'options' => ['width' => '40px'],
                    'format' => 'raw',
                    'value' => function ($data) use ($types) {
                        return isset($types[$data->source]) ? $types[$data->source] : Html::encode($data->source);
                    },
                ],
                [
                    'header' => Yii::t('AdminModule.views_user_index', 'Actions'),
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{resend}',
                    'options' => ['style' => 'width:80px; min-width:80px;'],
                    'buttons' => [
                        'resend' => function ($url, $model, $key) {
                            return Html::a(
                                '<i class="fa fa-envelope"></i>',
                                Url::to(['resend', 'id' => $model->id]),
                                ['class' => 'btn btn-primary btn-xs tt']
                            );
                        },
                    ],
                ],
            ]
    ]); ?>

</div>