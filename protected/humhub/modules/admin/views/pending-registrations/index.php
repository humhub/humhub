<?php

use humhub\modules\admin\widgets\ExportButton;
use humhub\widgets\GridView;
use yii\helpers\Html;

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
        ]
    ]) ?>

</div>