<?php

use humhub\compat\CAcknowledgeActiveForm;
use humhub\compat\CHtml;
use humhub\models\Setting;

?>

<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>

<?php $form = CAcknowledgeActiveForm::begin(['acknowledge' => true]); ?>

<?= $form->errorSummary($model); ?>

<div class="form-group">
    <div class="checkbox">
        <label>
            <?= $form->checkBox($model, 'enabled', ['readonly' => Yii::$app->settings->isFixed('proxy.enabled')]);
            echo $model->getAttributeLabel('enabled'); ?>
        </label>
    </div>
</div>

<hr>
<div class="form-group">
    <?= $form->labelEx($model, 'server'); ?>
    <?= $form->textField($model, 'server', ['class' => 'form-control']); ?>
</div>

<div class="form-group">
    <?= $form->labelEx($model, 'port'); ?>
    <?= $form->textField($model, 'port', ['class' => 'form-control']); ?>
</div>

<?php if (defined('CURLOPT_PROXYUSERNAME')) { ?>
    <div class="form-group">
        <?= $form->labelEx($model, 'user'); ?>
        <?= $form->textField($model, 'user', ['class' => 'form-control']); ?>
    </div>
<?php } ?>

<?php if (defined('CURLOPT_PROXYPASSWORD')) { ?>
    <div class="form-group">
        <?= $form->labelEx($model, 'password'); ?>
        <?= $form->textField($model, 'password', ['class' => 'form-control']); ?>
    </div>
<?php } ?>

<?php if (defined('CURLOPT_NOPROXY')) { ?>
    <div class="form-group">
        <?= $form->labelEx($model, 'noproxy'); ?>
        <?= $form->textArea($model, 'noproxy', ['class' => 'form-control', 'rows' => '4']); ?>
    </div>
<?php } ?>

<hr>
<?= CHtml::submitButton(Yii::t('AdminModule.settings', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>

<?= \humhub\widgets\DataSaved::widget(); ?>
<?php CAcknowledgeActiveForm::end(); ?>

<?php $this->endContent(); ?>
