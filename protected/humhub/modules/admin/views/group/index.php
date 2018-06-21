<?php

use yii\helpers\Url;
use yii\helpers\Html;
use humhub\widgets\GridView;
?>
<div class="panel-body">
    <div class="pull-right">
        <?= Html::a('<i class="fa fa-plus" aria-hidden="true"></i>&nbsp;&nbsp;' . Yii::t('AdminModule.views_groups_index', "Create new group"), Url::to(['edit']), ['class' => 'btn btn-sm btn-success']); ?>
    </div>
    
    <h4><?= Yii::t('AdminModule.views_group_index', 'Manage groups'); ?></h4>

    <div class="help-block">
        <?= Yii::t('AdminModule.views_groups_index', 'Users can be assigned to different groups (e.g. teams, departments etc.) with specific standard spaces, group managers and permissions.'); ?>
    </div>
</div>

<?= \humhub\modules\admin\widgets\GroupMenu::widget(); ?>

<div class="panel-body">

    <?php
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => ['class' => 'table table-hover'],
        'columns' => [
            'name',
            'description',
            [
                'attribute' => 'members',
                'label' => Yii::t('AdminModule.views_group_index', 'Members'),
                'format' => 'raw',
                'options' => ['style' => 'text-align:center;'],
                'value' => function ($data) {
                    return $data->getGroupUsers()->count();
                }
            ],
            [
                'class' => \humhub\libs\ActionColumn::class,
                'actions' => [
                    Yii::t('AdminModule.user', 'Settings') => ['edit'],
                    '---',
                    Yii::t('AdminModule.user', "Permissions") => ['manage-permissions'],
                    Yii::t('AdminModule.user', "Members") => ['manage-group-users'],
                ]
            ],
        ],
    ]);
    ?>
</div>