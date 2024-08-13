<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="panel panel-danger">

    <div class="panel-heading"><?= Yii::t('AdminModule.user', 'New approval requests'); ?></div>
    <div class="panel-body">
        <?= Yii::t('AdminModule.user', 'One or more user needs your approval as group admin.'); ?><br><br>
        <?= Html::a(Yii::t('AdminModule.user', 'Click here to review'), Url::to(['/admin/approval']), ['class' => 'btn btn-danger', 'data-ui-loader' => '']); ?>
    </div>

</div>