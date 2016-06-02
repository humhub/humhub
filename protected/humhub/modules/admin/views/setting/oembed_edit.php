<?php

use humhub\compat\CActiveForm;
use humhub\compat\CHtml;
use yii\helpers\Html;
use yii\helpers\Url;
?>

<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>

<div class="clearfix">
    <?php echo Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i>&nbsp;&nbsp;' . Yii::t('AdminModule.views_setting_oembed_edit', 'Back to overview'), 
                Url::to(['setting/oembed']), array('class' => 'btn btn-default pull-right')); ?>
    <h4 class="pull-left">
        <?php
        if ($prefix == "") {
            echo Yii::t('AdminModule.views_setting_oembed_edit', 'Add OEmbed provider');
        } else {
            echo Yii::t('AdminModule.views_setting_oembed_edit', 'Edit OEmbed provider');
        }
        ?>
    </h4>
</div>

<br />

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


<?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_oembed_edit', 'Save'), array('class' => 'btn btn-primary', 'data-ui-loader' => "")); ?>
<?php CActiveForm::end(); ?>        

<?php if ($prefix != ""): ?>
    <?php echo Html::a(Yii::t('AdminModule.views_setting_oembed_edit', 'Delete'), Url::to(['oembed-delete', 'prefix' => $prefix]), array('class' => 'btn btn-danger pull-right', 'data-method' => 'POST')); ?>
<?php endif; ?>

<?php $this->endContent(); ?>
