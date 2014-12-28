<div class="panel panel-default">
    <div
        class="panel-heading"><?php echo Yii::t('AdminModule.views_setting_privacy', '<strong>Privacy</strong> settings'); ?></div>
    <div class="panel-body">

        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'privacy-settings-form',
            'enableAjaxValidation' => false,
        ));
        ?>

        <?php echo $form->errorSummary($model); ?><br>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'defaultDisplayProfileFollowerInfo'); ?>
            <?php echo $form->dropDownList($model, 'defaultDisplayProfileFollowerInfo', $displayProfileInfoOptions, array('class' => 'form-control')); ?>
        </div>
        
		<div class="form-group">
        	<div class="checkbox">
            	<label>
               		<?php echo $form->checkbox($model, 'allowUserOverrideFollowerSetting', array('class' => 'form-control')); ?>
					<?php echo $model->getAttributeLabel('allowUserOverrideFollowerSetting'); ?>
            	</label>
            </div>
        </div>
        
        <hr />
        
        <div class="form-group">
            <?php echo $form->labelEx($model, 'defaultDisplayProfileFollowingInfo'); ?>
            <?php echo $form->dropDownList($model, 'defaultDisplayProfileFollowingInfo', $displayProfileInfoOptions, array('class' => 'form-control')); ?>
        </div>
        
        <div class="form-group">
            <div class="checkbox">
            	<label>
                	<?php echo $form->checkbox($model, 'allowUserOverrideFollowingSetting', array('class' => 'form-control')); ?>
					<?php echo $model->getAttributeLabel('allowUserOverrideFollowingSetting'); ?>
            	</label>
            </div>
        </div>

        <hr />
            	
        <div class="form-group">
            <?php echo $form->labelEx($model, 'defaultDisplayProfileSpaceInfo'); ?>
            <?php echo $form->dropDownList($model, 'defaultDisplayProfileSpaceInfo', $displayProfileInfoOptions, array('class' => 'form-control')); ?>
        </div>
        
		<div class="form-group">
        	<div class="checkbox">
            	<label>
               		<?php echo $form->checkbox($model, 'allowUserOverrideSpaceSetting', array('class' => 'form-control')); ?>
					<?php echo $model->getAttributeLabel('allowUserOverrideSpaceSetting'); ?>
            	</label>
            </div>
        </div>
        
        <hr>
        <?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_privacy', 'Save'), array('class' => 'btn btn-primary')); ?>

        <!-- show flash message after saving -->
        <?php $this->widget('application.widgets.DataSavedWidget'); ?>

        <?php $this->endWidget(); ?>

    </div>
</div>




