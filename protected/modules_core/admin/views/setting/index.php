<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_setting_index', '<strong>Basic</strong> settings'); ?></div>
    <div class="panel-body">

        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'basic-settings-form',
            'enableAjaxValidation' => false,
            'htmlOptions' => array('enctype'=>'multipart/form-data'),
        ));
        ?>

        <?php echo $form->errorSummary($model); ?>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'name'); ?>
            <?php echo $form->textField($model, 'name', array('class' => 'form-control', 'readonly' => HSetting::IsFixed('name'))); ?>
        </div>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'baseUrl'); ?>
            <?php echo $form->textField($model, 'baseUrl', array('class' => 'form-control', 'readonly' => HSetting::IsFixed('baseUrl'))); ?>
            <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_index', 'E.g. http://example.com/humhub'); ?></p>
        </div>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'defaultLanguage'); ?>
            <?php echo $form->dropDownList($model, 'defaultLanguage', Yii::app()->params['availableLanguages'], array('class' => 'form-control', 'readonly' => HSetting::IsFixed('defaultLanguage'))); ?>
        </div>


        <?php echo $form->labelEx($model, 'defaultSpaceGuid'); ?>
        <?php echo $form->textField($model, 'defaultSpaceGuid', array('class' => 'form-control', 'id' => 'space_select')); ?>
        <?php
        $this->widget('application.modules_core.space.widgets.SpacePickerWidget', array(
            'inputId' => 'space_select',
            'model' => $model,
            'attribute' => 'defaultSpaceGuid'
        ));
        ?>
        <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_index', 'New users will automatically added to these space(s).'); ?></p>


        <strong>Introduction tour</strong>
        <div class="form-group">
            <div class="checkbox">
                <label>
                    <?php echo $form->checkBox($model, 'tour'); ?> <?php echo $model->getAttributeLabel('tour'); ?>
                </label>
            </div>
        </div>
        
        
        
        <?php echo $form->labelEx($model, 'logo'); ?>
        <?php echo $form->fileField($model, 'logo', array('id' => 'logo', 'style' => 'display: none', 'name' => 'logo[]'));?>
        <?php echo $form->error($model, 'logo'); ?>

        <div class="image-upload-container" id ="logo-upload">
       
            <img class="img-rounded" id="logo-image"
                src="<?php if($logo->hasImage()) echo $logo->getUrl(); ?>"
                data-src="holder.js/140x140" alt="<?php echo Yii::app()->name; ?>" style="max-height: 40px;"/>

                <div class="image-upload-buttons" id="logo-upload-buttons" style="display: block;">
                    <a href="#" onclick="javascript:$('#logo').click();" class="btn btn-info btn-sm"><i
                            class="fa fa-cloud-upload"></i></a>
            
                    <?php
                    $this->widget('application.widgets.ModalConfirmWidget', array(
                                  'uniqueID' => 'modal_logoimagedelete',
                                  'linkOutput' => 'a',
                                  'title' => Yii::t('AdminModule.views_setting_index', '<strong>Confirm</strong> image deleting'),
                                  'message' => Yii::t('UserModule.views_setting_index', 'Do you really want to delete your logo image?'),
                                  'buttonTrue' => Yii::t('AdminModule.views_setting_index', 'Delete'),
                                  'buttonFalse' => Yii::t('AdminModule.views_setting_index', 'Cancel'),
                                  'linkContent' => '<i class="fa fa-times"></i>',
                                  'class' => 'btn btn-danger btn-sm',
                                  'style' => $logo->hasImage() ? '' : 'display: none;',
                                  'linkHref' => $this->createUrl("//admin/setting/deleteLogoImage"),
                                  'confirmJS' => 'function(jsonResp) { resetLogoImage(jsonResp); }'
                                 )); 
                    ?>
                </div>
        </div>
        
        <hr>

        <?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_index', 'Save'), array('class' => 'btn btn-primary')); ?>

        <!-- show flash message after saving -->
        <?php $this->widget('application.widgets.DataSavedWidget'); ?>

        <?php $this->endWidget(); ?>

    </div>
</div>

