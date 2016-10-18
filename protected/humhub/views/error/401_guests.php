<?php

use yii\helpers\Html;
?>
<div class="container">
    <div class="panel panel-danger">
        <div class="panel-heading">
            <?php echo Yii::t('error', "<strong>Login</strong> required"); ?>
        </div>
        <div class="panel-body">

            <strong><?php echo Html::encode($message); ?></strong>

            <br />
            <hr>

            <?php echo Html::a(Yii::t('base', 'Login'), Yii::$app->user->loginUrl, array('class' => 'btn btn-info', 'data-target' => '#globalModal')); ?>
            <a href="javascript:history.back();" class="btn btn-primary  pull-right"><?php echo Yii::t('base', 'Back'); ?></a>
        </div>
    </div>
</div>
