<?php

use humhub\compat\CHtml;
use humhub\compat\CActiveForm;
?>


<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_group_delete', '<strong>Delete</strong> group'); ?></div>
    <div class="panel-body">

        <p>
            <?php echo Yii::t('AdminModule.views_group_delete', 'To delete the group <strong>"{group}"</strong> you need to set an alternative group for existing users:', array('{group}' => CHtml::encode($group->name))); ?>
        </p>

        <?php $form = CActiveForm::begin(); ?>

        <?php echo $form->errorSummary($model); ?>

        <div class="form-group">
            <?php echo $form->dropDownList($model, 'group_id', $alternativeGroups, array('class' => 'form-control')); ?>
            <?php echo $form->error($model, 'group_id'); ?>
        </div>

        <hr>
        <?php echo CHtml::submitButton(Yii::t('AdminModule.views_group_delete', 'Delete group'), array('class' => 'btn btn-danger')); ?>

        <?php CActiveForm::end(); ?>
    </div></div>




