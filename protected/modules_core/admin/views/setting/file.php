<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_setting_file', '<strong>File</strong> settings'); ?></div>
    <div class="panel-body">


        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'file-settings-form',
            'enableAjaxValidation' => false,
        ));
        ?>

        <?php echo $form->errorSummary($model); ?>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'imageMagickPath'); ?>
            <?php echo $form->textField($model, 'imageMagickPath', array('class' => 'form-control', 'readonly' => HSetting::IsFixed('imageMagickPath', 'file'))); ?>
            <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_file', 'Current Image Libary: {currentImageLibary}', array('{currentImageLibary}' => $currentImageLibary)); ?></p>
        </div>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'maxFileSize'); ?>
            <?php echo $form->textField($model, 'maxFileSize', array('class' => 'form-control', 'readonly' => HSetting::IsFixed('maxFileSize', 'file'))); ?>
            <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_file', 'PHP reported a maximum of {maxUploadSize} MB', array('{maxUploadSize}' => $maxUploadSize)); ?></p>
        </div>

        <div class="form-group">
            <div class="checkbox">
                <label>
                    <?php echo $form->checkBox($model, 'useXSendfile', array('disabled' => HSetting::IsFixed('useXSendfile', 'file'))); ?>
                    <?php echo $model->getAttributeLabel('useXSendfile'); ?>
                </label>
            </div>
        </div>
        
		<div class="form-group">
            <?php echo $form->labelEx($model, 'maxPreviewImageWidth'); ?>
            <?php echo $form->textField($model, 'maxPreviewImageWidth', array('class' => 'form-control', 'readonly' => HSetting::IsFixed('maxPreviewImageWidth', 'file'))); ?>
        	<p class="help-block"><?php echo Yii::t('AdminModule.views_setting_file', 'If not set, width will default to 200px.') ?></p>
        </div>
        
        <div class="form-group">
            <?php echo $form->labelEx($model, 'maxPreviewImageHeight'); ?>
            <?php echo $form->textField($model, 'maxPreviewImageHeight', array('class' => 'form-control', 'readonly' => HSetting::IsFixed('maxPreviewImageHeight', 'file'))); ?>
        	     	<p class="help-block"><?php echo Yii::t('AdminModule.views_setting_file', 'If not set, height will default to 200px.') ?></p>
        </div>
        
        <div class="form-group">
            <div class="checkbox">
                <label>
                    <?php echo $form->checkBox($model, 'hideImageFileInfo', array('disabled' => HSetting::IsFixed('hideImageFileInfo', 'file'))); ?>
                    <?php echo $model->getAttributeLabel('hideImageFileInfo'); ?>
                </label>
            </div>
        </div>
        
        <div class="form-group">
            <?php echo $form->labelEx($model, 'allowedExtensions'); ?>
            <?php echo $form->textField($model, 'allowedExtensions', array('class' => 'form-control')); ?>
            <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_file', 'Comma separated list. Leave empty to allow all.'); ?></p>
            
            
        </div>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'showFilesWidgetBlacklist'); ?>
            <?php echo $form->textField($model, 'showFilesWidgetBlacklist', array('class' => 'form-control')); ?>
            <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_file', 'Comma separated list. Leave empty to show file list for all objects on wall.') ?></p>
        </div>
        
        <hr>

        <?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_file', 'Save'), array('class' => 'btn btn-primary')); ?>

        <!-- show flash message after saving -->
        <?php $this->widget('application.widgets.DataSavedWidget'); ?>

        <?php $this->endWidget(); ?>

    </div>
</div>






