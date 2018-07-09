<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var $model \humhub\modules\user\models\Invite */
?>
<div class="panel-body">
    <h4><?= Yii::t('AdminModule.views_invite_delete', 'Delete invitation?'); ?></h4>
    <br>

    <?= \yii\widgets\DetailView::widget([
        'model' => $model,
        'attributes' => [
            'email:email',
            [
                'label' => 'Invited by',
                'attribute' => 'originator.username',
            ],
            [
                'label' => 'Invited at',
                'attribute' => 'created_at',
                'format' => 'datetime',
            ],
        ],
    ]); ?>

    <br>
    <br>

    <?= Html::a(
        Yii::t('AdminModule.views_approval_resend', 'Delete invitation'),
        Url::to(['/admin/pending-registrations/delete', 'id' => $model->id]),
        ['class' => 'btn btn-danger', 'data-method' => 'POST']
    ); ?>
    <?= Html::a(
        Yii::t('AdminModule.views_approval_resend', 'Cancel'),
        Url::to(['/admin/pending-registrations']),
        ['class' => 'btn btn-primary pull-right']
    ); ?>
</div>