<?php
/**
 * Delete View of a group
 *
 * @property Group $group the group object to delete
 * @property AdminDeleteGroupForm $model is the model of the deletion form
 *
 * @todo Cleanup Group View
 * @todo Add Back Button
 *
 * @package humhub.modules_core.admin
 * @since 0.5
 */
?>
<div id="middle">
    <div class="panel_middle">

        <div class="account_title"><?php echo Yii::t('AdminModule.base', 'Delete group: {group}', array('group' => $group->name)); ?></div>

        <div class="content m20">

            <p class="note"><?php echo Yii::t('AdminModule.base', 'You need to set an alternative group for existing users'); ?></p>

        </div>

        <div class="form">

            <?php
            $form = $this->beginWidget('CActiveForm', array(
                'id' => 'admin-deleteGroup-form',
                'enableAjaxValidation' => true,
                'htmlOptions' => array('enctype' => 'multipart/form-data'),
            ));
            ?>

            <?php echo $form->errorSummary($model); ?>

            <div class="row">
                <?php
                $groupModels = Group::model()->findAll('id !=' . $group->id);
                $list = CHtml::listData($groupModels, 'id', 'name');
                ?>

                <?php echo $form->labelEx($model, 'group_id'); ?><br>
                <?php echo $form->dropDownList($model, 'group_id', $list, array('class' => 'dropdown-select')); ?>
                <?php echo $form->error($model, 'group_id'); ?>
            </div>

            <div class="clearFloats"></div>

            <div class="p20">
                <?php echo CHtml::submitButton(Yii::t('AdminModule.base', 'Delete group'), array('class' => 'input_button')); ?>

            </div>

            <?php $this->endWidget(); ?>

        </div><!-- form -->




    </div>
</div>

