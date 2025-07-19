<?php

use humhub\helpers\Html;
use humhub\modules\admin\widgets\PrerequisitesList;
use yii\helpers\Url;

?>
<p><?= Yii::t('AdminModule.information', 'Checking HumHub software prerequisites.'); ?></p>

<?= PrerequisitesList::widget(); ?>
<br>

<?= Html::a(Yii::t('AdminModule.information', 'Re-Run tests'), Url::to(['prerequisites']), ['class' => 'btn btn-primary']); ?>
