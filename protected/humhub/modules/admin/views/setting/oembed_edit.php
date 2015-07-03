<?php

use humhub\compat\CActiveForm;
use humhub\compat\CHtml;
use yii\helpers\Html;
use humhub\models\Setting;
use yii\helpers\Url;
?>
<div class="panel panel-default">
    <div
        class="panel-heading"><?php
            if ($prefix == "") {
                echo Yii::t('AdminModule.views_setting_oembed_edit', '<strong>Add</strong> OEmbed Provider');
            } else {
                echo Yii::t('AdminModule.views_setting_oembed_edit', '<strong>Edit</strong> OEmbed Provider');
            }
            ?></div>

    <div class="panel-body">

        <?php $form = CActiveForm::begin(['id' => 'authentication-settings-form']); ?>


        <?php echo $form->errorSummary($model); ?>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'prefix'); ?>
            <?php echo $form->textField($model, 'prefix', array('class' => 'form-control')); ?>
            <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_oembed_edit', 'Url Prefix without http:// or https:// (e.g. youtube.com)'); ?></p>            
        </div>        

        <div class="form-group">
            <?php echo $form->labelEx($model, 'endpoint'); ?>
            <?php echo $form->textField($model, 'endpoint', array('class' => 'form-control')); ?>
            <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_oembed_edit', 'Use %url% as placeholder for URL. Format needs to be JSON. (e.g. http://www.youtube.com/oembed?url=%url%&format=json)'); ?></p>            
        </div>        


        <?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_oembed_edit', 'Save'), array('class' => 'btn btn-primary')); ?>
        <?php CActiveForm::end(); ?>        

        <?php if ($prefix != ""): ?>
            <?php echo Html::a(Yii::t('AdminModule.views_setting_oembed_edit', 'Delete'), Url::to(['oembed-delete', 'prefix' => $prefix]), array('class' => 'btn btn-danger pull-right', 'data-method' => 'POST')); ?>
        <?php endif; ?>

    </div>
</div>








