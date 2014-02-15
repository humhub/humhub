<div class="panel panel-default">
    <div class="panel-heading">
        <?php echo Yii::t('SpaceModule.base', 'Image tuning'); ?>
    </div>
    <div class="panel-body">
        <p><?php echo Yii::t('SpaceModule.base', 'Select the area of your image you want to save as workspace avatar and click <strong>Save</strong>.'); ?></p><br>

        <?php $form = $this->beginWidget('CActiveForm', array('id' => 'workspace-crop-image-form','enableAjaxValidation' => false)); ?>
        <?php echo $form->errorSummary($model); ?>
        <?php echo $form->hiddenField($model, 'cropX'); ?>
        <?php echo $form->hiddenField($model, 'cropY'); ?>
        <?php echo $form->hiddenField($model, 'cropW'); ?>
        <?php echo $form->hiddenField($model, 'cropH'); ?>


        <style>
                /* Dirty Workaround against bootstrap and jcrop */
            img {max-width: none}
        </style>

        <div id="cropimage">
            <?php
            $this->widget('ext.yii-jcrop.jCropWidget', array(
                    'imageUrl' => $profileImage->getUrl('_org') . "?nocache=" . time(),
                    'formElementX' => 'CropProfileImageForm_cropX',
                    'formElementY' => 'CropProfileImageForm_cropY',
                    'formElementWidth' => 'CropProfileImageForm_cropW',
                    'formElementHeight' => 'CropProfileImageForm_cropH',
                    'jCropOptions' => array(
                        'aspectRatio' => 1,
                        'boxWidth' => 500,
                        'boxHeight' => 500,
                        'setSelect' => array(0, 0, 200, 200),
                    ),
                )
            );
            ?>
        </div>
        <hr>
        <?php echo CHtml::submitButton(Yii::t('SpaceModule.base', 'Save'), array('class' => 'btn btn-primary')); ?>

        <?php $this->endWidget(); ?>
    </div>
</div>




