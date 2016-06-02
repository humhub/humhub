<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="panel-body">
    <h4><?php echo Yii::t('AdminModule.views_user_delete', 'Delete user'); ?></h4>

    <p>
        <?php echo Yii::t('AdminModule.views_user_delete', 'Are you sure you want to delete this user? If this user is owner of some spaces, <b>you</b> will become owner of these spaces.'); ?>
    </p>

    <?php
    echo \yii\widgets\DetailView::widget([
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

    <br/>
    <?php echo Html::a(Yii::t('AdminModule.views_user_delete', 'Delete user'), Url::toRoute(['/admin/user/delete', 'id' => $model->id, 'doit' => 2]), array('class' => 'btn btn-danger', 'data-method' => 'POST')); ?>
    &nbsp;
    <?php echo Html::a(Yii::t('AdminModule.views_user_delete', 'Back'), Url::toRoute(['/admin/user/edit', 'id' => $model->id]), array('class' => 'btn btn-primary')); ?>
</div>
