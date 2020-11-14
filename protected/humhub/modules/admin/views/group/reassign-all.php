<?php

use humhub\modules\user\models\Group;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var $model Group */
?>
<div class="panel-body">
    <h4><?= Yii::t('AdminModule.user', 'Reassign default spaces to all users?'); ?></h4>
    <br>
    <br>
    <br>

    <?= Html::a(
        Yii::t('AdminModule.user', 'Reassign All'),
        Url::to(['/admin/group/reassign-all', 'id' => $model->id]),
        ['class' => 'btn btn-info', 'data-method' => 'POST']
    ); ?>
    <?= Html::a(
        Yii::t('AdminModule.user', 'Cancel'),
        Url::to(['/admin/group/edit', 'id' => $model->id]),
        ['class' => 'btn btn-primary pull-right']
    ); ?>
</div>
