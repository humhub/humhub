<?php


use yii\widgets\ActiveForm;
use yii\bootstrap\Html;
?>
<div id="name-form" class="panel panel-default animated fadeIn">

    <div class="panel-heading">
        <?php echo Yii::t('InstallerModule.views_config_sample-data', '<strong>Example</strong> contents'); ?>
    </div>

    <div class="panel-body">

        <p><?php echo Yii::t('InstallerModule.views_config_sample-data', 'To avoid a blank dashboard after your initial login, HumHub can install example contents for you. Those will give you a nice general view of how HumHub works. You can always delete the individual contents.'); ?></p>
        <br>

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'sampleData')->checkbox(); ?>
        <hr>

        <?php echo Html::submitButton(Yii::t('base', 'Next'), array('class' => 'btn btn-primary', 'data-ui-loader' => '')); ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>


