<div class="panel-heading">
    <?php echo Yii::t('UserModule.views_account_editPrivacy', '<strong>Privacy</strong> settings'); ?>
</div>
<div class="panel-body">
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'user-form',
        'enableAjaxValidation' => false,
    ));
    ?>

    <?php //echo $form->errorSummary($model); ?>

    <?php if (HSetting::Get('allowUserOverrideFollowerSetting', 'privacy_default')) : ?>
    	<div class="form-group">
    		<?php echo $form->labelEx($model, 'displayProfileFollowerInfo'); ?>
        	<?php echo $form->dropDownList($model, 'displayProfileFollowerInfo', $displayProfileInfoOptions, array('class' => 'form-control')); ?>
    	</div>
	<?php endif ?>
	
	<?php if (HSetting::Get('allowUserOverrideFollowingSetting', 'privacy_default')) : ?>
  		<div class="form-group">
    		<?php echo $form->labelEx($model, 'displayProfileFollowingInfo'); ?>
        	<?php echo $form->dropDownList($model, 'displayProfileFollowingInfo', $displayProfileInfoOptions, array('class' => 'form-control')); ?>
    	</div>
    <?php endif ?>
    
    <?php if (HSetting::Get('allowUserOverrideSpaceSetting', 'privacy_default')) : ?>
    	<div class="form-group">
    		<?php echo $form->labelEx($model, 'displayProfileSpaceInfo'); ?>
        	<?php echo $form->dropDownList($model, 'displayProfileSpaceInfo', $displayProfileInfoOptions, array('class' => 'form-control')); ?>
    	</div>
   	<?php endif ?>
   	
    <hr />

    <?php echo CHtml::submitButton(Yii::t('UserModule.views_account_editPrivacy', 'Save'), array('class' => 'btn btn-primary')); ?>

    <!-- show flash message after saving -->
    <?php $this->widget('application.widgets.DataSavedWidget'); ?>

    <?php $this->endWidget(); ?>
</div>



