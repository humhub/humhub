<?php

use humhub\helpers\Html;
use yii\helpers\Url;

?>
<div class="card content-card content-card-danger">

    <div class="card-header"><div class="card-title"><?= Yii::t('AdminModule.user', 'New approval requests'); ?></div></div>
    <div class="card-body">
        <?= Yii::t('AdminModule.user', 'One or more user needs your approval as group admin.'); ?><br><br>
        <?= Html::a(Yii::t('AdminModule.user', 'Click here to review'), Url::to(['/admin/approval']), ['class' => 'btn btn-danger', 'data-ui-loader' => '']); ?>
    </div>

</div>
