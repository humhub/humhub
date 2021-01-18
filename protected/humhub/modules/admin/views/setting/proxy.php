<?php

use humhub\modules\ui\form\widgets\ActiveForm;
use yii\helpers\Html;

?>

<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>

<?php $form = ActiveForm::begin(['acknowledge' => true]); ?>

<?= $form->errorSummary($model); ?>

<div class="form-group">
    <div class="checkbox">
        <?= $form->field($model, 'enabled')->checkbox(['readonly' => Yii::$app->settings->isFixed('proxy.enabled')]); ?>
    </div>
</div>

<hr>
<div class="form-group">
    <?= $form->field($model, 'server')->textInput(['class' => 'form-control']); ?>
</div>

<div class="form-group">
    <?= $form->field($model, 'port')->textInput(['class' => 'form-control']); ?>
</div>

<?php if (defined('CURLOPT_PROXYUSERNAME')) { ?>
    <div class="form-group">
        <?= $form->field($model, 'user')->textInput(['class' => 'form-control']); ?>
    </div>
<?php } ?>

<?php if (defined('CURLOPT_PROXYPASSWORD')) { ?>
    <div class="form-group">
        <?= $form->field($model, 'password')->textInput(['class' => 'form-control']); ?>
    </div>
<?php } ?>

<?php if (defined('CURLOPT_NOPROXY')) { ?>
    <div class="form-group">
        <?= $form->field($model, 'noproxy')->textarea(['class' => 'form-control', 'rows' => '4']); ?>
    </div>
<?php } ?>

<hr>
<?= Html::submitButton(Yii::t('AdminModule.settings', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>

<?= \humhub\widgets\DataSaved::widget(); ?>
<?php ActiveForm::end(); ?>

<?php $this->endContent(); ?>
