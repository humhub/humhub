<?php

use humhub\helpers\Html;
use humhub\modules\admin\models\forms\LogFilterForm;
use humhub\modules\ui\view\components\View;
use humhub\widgets\form\ActiveForm;

/* @var $this View */
/* @var $model LogFilterForm */
?>

<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->errorSummary($model); ?>

<?= $form->field($model, 'enabled')->checkbox(['readonly' => Yii::$app->settings->isFixed('proxy.enabled')]); ?>

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

<?php ActiveForm::end(); ?>

<?php $this->endContent(); ?>
