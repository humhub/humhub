<?php

use humhub\modules\admin\models\forms\CacheSettingsForm;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $cacheTypes [] */
/* @var $model CacheSettingsForm */

?>

<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>

<?php $form = ActiveForm::begin(['acknowledge' => true]); ?>

<?php if (!$model->isTypeFixed): ?>
    <?= $form->field($model, 'type')->dropDownList($cacheTypes) ?>
<?php else: ?>
    <?= $form->field($model, 'type')->textInput([
        'value' => ArrayHelper::getValue($cacheTypes, $model->fixedTypeValue, $model->fixedTypeValue),
        'readonly' => true,
        'title' => Yii::t('AdminModule.settings', 'Specified in the configuration file'),
        'class' => 'form-control tt',
    ]) ?>
<?php endif; ?>

<?= $form->field($model, 'expireTime')->textInput(['readonly' => Yii::$app->settings->isFixed('cacheExpireTime')]) ?>

<hr>
<?= Button::primary(Yii::t('AdminModule.settings', 'Save & Flush Caches'))->submit() ?>

<?php ActiveForm::end(); ?>

<?php $this->endContent(); ?>
