<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="panel-body">
    <h4><?= Yii::t('AdminModule.views_user_delete', 'Confirm user deletion'); ?></h4>
    <br>
    <p><?= Yii::t('AdminModule.views_user_delete', 'Are you sure you want to delete this user?'); ?></p>

    <ul>
        <li><?= Yii::t('AdminModule.views_user_delete', 'All created contents of this user will be <b>deleted</b>.'); ?></li>
        <li><?= Yii::t('AdminModule.views_user_delete', 'If this user is owner of some spaces, <b>you</b> will automatically become owner of these spaces.'); ?></li>
    </ul>

    <br>

    <?=
    \yii\widgets\DetailView::widget([
        'model' => $model,
        'attributes' => [
            'username',
            'profile.firstname',
            'profile.lastname',
            'email:email',
            'created_at:datetime',
        ],
    ]);
    ?>

    <br>
    <br>

    <?= Html::a(Yii::t('AdminModule.views_user_delete', 'Delete user'), Url::to(['/admin/user/delete', 'id' => $model->id, 'doit' => 2]), ['class' => 'btn btn-danger', 'data-method' => 'POST']); ?>
    <?= Html::a(Yii::t('AdminModule.views_user_delete', 'Cancel'), Url::to(['/admin/user/edit', 'id' => $model->id]), ['class' => 'btn btn-primary pull-right']); ?>
</div>