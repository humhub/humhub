<?php

use humhub\modules\ui\form\widgets\ActiveForm;
use yii\helpers\Html;

?>

<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->errorSummary($model); ?>

<div class="checkbox">
    <?= $form->field($model, 'enabled')->checkbox(['readonly' => Yii::$app->settings->isFixed('proxy.enabled')]); ?>
</div>

<hr>

<?= $form->field($model, 'server')->textInput(['class' => 'form-control']); ?>

<?= $form->field($model, 'port')->textInput(['class' => 'form-control']); ?>


<?php if (defined('CURLOPT_PROXYUSERNAME')) { ?>
    <?= $form->field($model, 'user')->textInput(['class' => 'form-control']); ?>
<?php } ?>

<?php if (defined('CURLOPT_PROXYPASSWORD')) { ?>
    <?= $form->field($model, 'password')->textInput(['class' => 'form-control']); ?>
<?php } ?>

<?php if (defined('CURLOPT_NOPROXY')) { ?>
    <?= $form->field($model, 'noproxy')->textarea(['class' => 'form-control', 'rows' => '4']); ?>
<?php } ?>

<hr>
<?= Html::submitButton(Yii::t('AdminModule.settings', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>

<?= \humhub\widgets\DataSaved::widget(); ?>
<?php ActiveForm::end(); ?>

<?php $this->endContent(); ?>
