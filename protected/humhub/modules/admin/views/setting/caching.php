<?php

use humhub\modules\admin\libs\CacheHelper;
use humhub\widgets\bootstrap\Button;
use yii\helpers\Html;

/* @var $model CacheHelper */

?>

<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>

<?= Html::beginForm() ?>
<?= Button::primary(Yii::t('AdminModule.settings', 'Flush Caches'))->submit()->options(['name' => 'flush', 'value' => 1]) ?>
<?= Html::endForm() ?>

<?php $this->endContent(); ?>
