<?php

use yii\helpers\Url;
use yii\helpers\Html;
use humhub\widgets\GridView;
?>
<div class="panel-body">
    
<h4><?php echo Yii::t('AdminModule.views_group_index', 'Manage groups'); ?></h4>

<p>
    <?php echo Yii::t('AdminModule.views_groups_index', 'You can split users into different groups (for teams, departments etc.) and define standard spaces and admins for them.'); ?>
</p>

<br />

<?= \humhub\modules\admin\widgets\GroupMenu::widget(); ?>
<div class="pull-right">
    <?php echo Html::a('<i class="fa fa-plus" aria-hidden="true"></i>&nbsp;&nbsp;' . Yii::t('AdminModule.views_groups_index', "Create new group"), Url::to(['edit']), array('class' => 'btn btn-success')); ?>
</div>

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
            'header' => Yii::t('AdminModule.views_group_index', 'Actions'),
            'class' => 'yii\grid\ActionColumn',
            'options' => ['width' => '80px'],
            'buttons' => [
                'view' => function() {
                    return;
                },
                'delete' => function() {
                    return;
                },
                'update' => function($url, $model) {
                    return Html::a('<i class="fa fa-pencil"></i>', Url::toRoute(['edit', 'id' => $model->id]), ['class' => 'btn btn-primary btn-xs tt']);
                },
                    ],
                ],
            ],
        ]);
        ?>           
</div>