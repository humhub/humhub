<?php

use humhub\modules\admin\models\forms\CacheSettingsForm;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\widgets\Button;

/* @var $cacheTypes [] */
/* @var $model CacheSettingsForm */

?>

<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>

<?php $form = ActiveForm::begin(['acknowledge' => true]); ?>

<?php if (!Yii::$app->settings->isFixed('cacheClass')): ?>
    <?= $form->field($model, 'type')->dropDownList($cacheTypes, ['readonly' => Yii::$app->settings->isFixed('cacheClass')]) ?>
<?php endif; ?>

<?= $form->field($model, 'expireTime')->textInput(['readonly' => Yii::$app->settings->isFixed('cacheExpireTime')]) ?>

<hr>
<?= Button::primary(Yii::t('AdminModule.settings', 'Save & Flush Caches'))->submit() ?>

<?php ActiveForm::end(); ?>

<?php $this->endContent(); ?>
