<?php

use humhub\helpers\Html;
use humhub\widgets\SidebarCard;
use yii\helpers\Url;

?>
<?php SidebarCard::begin([
    'title' => Yii::t('AdminModule.user', 'New approval requests'),
    'collapsible' => false,
    'options' => ['class' => 'content-card-danger'],
]) ?>
    <?= Yii::t('AdminModule.user', 'One or more user needs your approval as group admin.'); ?><br><br>
    <?= Html::a(Yii::t('AdminModule.user', 'Click here to review'), Url::to(['/admin/approval']), ['class' => 'btn btn-danger', 'data-ui-loader' => '']); ?>
<?php SidebarCard::end() ?>
