<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var $model \humhub\modules\user\models\Invite */
?>
<div class="panel-body">
    <h4><?= Yii::t('AdminModule.user', 'Delete all invitations?'); ?></h4>
    <br>

    <?= Html::a(
        Yii::t('AdminModule.user', 'Delete all invitations'),
        Url::to(['/admin/pending-registrations/delete-all']),
        ['class' => 'btn btn-danger', 'data-method' => 'POST']
    ); ?>
    <?= Html::a(
        Yii::t('AdminModule.user', 'Cancel'),
        Url::to(['/admin/pending-registrations']),
        ['class' => 'btn btn-primary pull-right']
    ); ?>
</div>
