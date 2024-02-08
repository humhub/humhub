<?php

use humhub\modules\user\models\Invite;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/** @var $model Invite */
?>
<div class="panel-body">
    <h4><?= Yii::t('AdminModule.user', 'Send invitation email again?'); ?></h4>
    <br>

    <?= DetailView::widget([
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
        Yii::t('AdminModule.user', 'Send invitation email'),
        Url::to(['/admin/pending-registrations/resend', 'id' => $model->id]),
        ['class' => 'btn btn-danger', 'data-method' => 'POST']
    ); ?>
    <?= Html::a(
        Yii::t('AdminModule.user', 'Cancel'),
        Url::to(['/admin/pending-registrations']),
        ['class' => 'btn btn-primary pull-right']
    ); ?>
</div>
