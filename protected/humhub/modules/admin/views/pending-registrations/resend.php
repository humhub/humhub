<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="panel-body">
    <h4><?= Yii::t('AdminModule.views_approval_resend', 'Send invitation email again?'); ?></h4>
    <br>

    <?= \yii\widgets\DetailView::widget([
        'model' => $model,
        'attributes' => [
            'email:email',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]); ?>

    <br>
    <br>

    <?= Html::a(
        Yii::t('AdminModule.views_approval_resend', 'Send invitation email'),
        Url::to(['/admin/pending-registrations/resend', 'id' => $model->id]),
        ['class' => 'btn btn-danger', 'data-method' => 'POST']
    ); ?>
    <?= Html::a(
        Yii::t('AdminModule.views_approval_resend', 'Cancel'),
        Url::to(['/admin/pending-registrations']),
        ['class' => 'btn btn-primary pull-right']
    ); ?>
</div>