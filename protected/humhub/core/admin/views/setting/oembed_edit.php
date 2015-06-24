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

        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'oembed-edit-form',
            'enableAjaxValidation' => false,
        ));
        ?>

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

        <?php if ($prefix != ""): ?>
            <?php echo HHtml::postLink(Yii::t('AdminModule.views_setting_oembed_edit', 'Delete'), $this->createUrl('oembedDelete'), array('class' => 'btn btn-danger pull-right'), array('prefix' => $prefix)); ?>
        <?php endif; ?>
        <?php $this->endWidget(); ?>

    </div>
</div>








