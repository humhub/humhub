<div class="panel-heading">
    <?php echo Yii::t('UserModule.base', 'Upload a profile image'); ?>
</div>
<div class="panel-body">
    <p><?php echo Yii::t('UserModule.base', 'Please upload a photo. You can upload <strong>JPEG</strong> and <strong>PNG</strong> images.'); ?></p>
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'account-changeimage-form',
        'enableAjaxValidation' => false,
        'htmlOptions' => array('enctype' => 'multipart/form-data')));
    ?>

    <?php echo $form->fileField($model, 'image', array('class' => 'form-control')); ?>

    <hr>
    <?php echo CHtml::submitButton(Yii::t('UserModule.base', 'Upload'), array('class' => 'btn btn-primary')); ?>

    <?php if ($user->getProfileImage()->hasImage()) : ?>
        <div class="pull-right">
            <?php echo CHtml::link(Yii::t('UserModule.base', 'Modify current image'), $this->createUrl('//user/account/cropImage'), array('class' => 'btn btn-primary')); ?>
            <?php echo CHtml::link(Yii::t('UserModule.base', 'Delete image'), $this->createUrl('//user/account/deleteImage'), array('class' => 'btn btn-danger')); ?>

        </div>
    <?php endif; ?>


    <?php $this->endWidget(); ?>
</div>




