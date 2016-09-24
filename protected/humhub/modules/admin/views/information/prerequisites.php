<?php

use yii\helpers\Html;
use yii\helpers\Url;
use humhub\modules\admin\widgets\PrerequisitesList;
?>
<p><?php echo Yii::t('AdminModule.views_setting_selftest', 'Checking HumHub software prerequisites.'); ?></p>

<?= PrerequisitesList::widget(); ?>
<br>

<?php echo Html::a(Yii::t('AdminModule.views_setting_selftest', 'Re-Run tests'), Url::to(['prerequisites']), array('class' => 'btn btn-primary')); ?>

