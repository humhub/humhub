<?php

use yii\helpers\Html;
use yii\helpers\Url;
use humhub\modules\admin\widgets\PrerequisitesList;
?>
<p><?= Yii::t('AdminModule.views_setting_selftest', 'Checking HumHub software prerequisites.'); ?></p>

<?= PrerequisitesList::widget(); ?>
<br>

<?= Html::a(Yii::t('AdminModule.views_setting_selftest', 'Re-Run tests'), Url::to(['prerequisites']), ['class' => 'btn btn-primary']); ?>