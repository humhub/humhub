<div class="panel panel-default">
    <div class="panel-heading">
        <?php echo Yii::t('SpaceModule.base', 'Upload a space image'); ?>
    </div>
    <div class="panel-body">
        <p><?php echo Yii::t('SpaceModule.base', 'Please upload a photo related to your space. You can upload <strong>JPEG</strong> and <strong>PNG</strong> images.'); ?></p><br>

        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'space-changeimage-form',
            'enableAjaxValidation' => false,
            'htmlOptions' => array('enctype' => 'multipart/form-data')
        ));
        ?>

        <?php echo $form->fileField($model, 'image', array('class' => 'span12')); ?>

        <hr>
        <?php echo CHtml::submitButton(Yii::t('SpaceModule.base', 'Upload'), array('class' => 'btn btn-primary')); ?>


        <?php if ($this->getSpace()->getProfileImage()->hasImage()) : ?>
            <div class="pull-right">
                <?php echo CHtml::link(Yii::t('SpaceModule.base', 'Modify current image'), $this->createUrl('//space/admin/cropImage', array('sguid' => $this->getSpace()->guid)), array('class' => 'btn btn-primary')); ?>
                <?php echo CHtml::link(Yii::t('SpaceModule.base', 'Delete image'), $this->createUrl('//space/admin/deleteImage', array('sguid' => $this->getSpace()->guid)), array('class' => 'btn btn-danger')); ?>

            </div>
        <?php endif; ?>

        <?php $this->endWidget(); ?>
    </div>
</div>




