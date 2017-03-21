<?php

use yii\helpers\Html;
?>
<div class="container">
    <div class="panel panel-danger">
        <div class="panel-heading">
            <?= Yii::t('error', "<strong>Login</strong> required"); ?>
        </div>
        <div class="panel-body">

            <strong><?= Html::encode($message); ?></strong>

            <br>
            <hr>

            <?= Html::a(Yii::t('base', 'Login'), Yii::$app->user->loginUrl, array('class' => 'btn btn-info', 'data-target' => '#globalModal')); ?>
            <a href="javascript:history.back();" class="btn btn-primary  pull-right"><?= Yii::t('base', 'Back'); ?></a>
        </div>
    </div>
</div>
