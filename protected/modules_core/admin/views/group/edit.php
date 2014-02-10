<?php
/**
 * Form to create or edit a group
 *
 * @property Group $group the group object
 *
 * @todo Also add a picker for default space
 * @package humhub.modules_core.admin
 * @since 0.5
 */
?>


<?php if (!$group->isNewRecord) : ?>
    <h1><?php echo Yii::t('AdminModule.base', 'Edit group'); ?></h1>
<?php else: ?>
    <h1><?php echo Yii::t('AdminModule.base', 'Create new group'); ?></h1>
<?php endif; ?>

<?php
$form = $this->beginWidget('CActiveForm', array(
    'id' => 'admin-editGroup-form',
    'enableAjaxValidation' => false,
));
?>

<?php echo $form->errorSummary($model); ?>

<div class="form-group">
    <?php echo $form->labelEx($model, 'name'); ?>
    <?php echo $form->textField($model, 'name', array('class' => 'form-control', 'placeholder' => Yii::t('AdminModule.base', 'Group name'))); ?>
</div>

<div class="form-group">
    <?php echo $form->labelEx($model, 'description'); ?>
    <?php echo $form->textArea($model, 'description', array('class' => 'form-control', 'rows' => '5', 'placeholder' => Yii::t('AdminModule.base', 'Description'))); ?>
</div>

<?php echo $form->labelEx($model, 'defaultSpaceGuid'); ?>
<?php echo $form->textField($model, 'defaultSpaceGuid', array('class' => 'form-control', 'id' => 'space_select')); ?>

<?php
$this->widget('application.modules_core.space.widgets.SpacePickerWidget', array(
    'inputId' => 'space_select',
    'maxSpaces' => 1,
    'model' => $model,
    'attribute' => 'defaultSpaceGuid'
));
?>
<br/>



<?php if ($group->space) : ?>
    <p>Currently assigned to: <?php echo $group->space->name; ?></p>
<?php endif; ?>
<!-- /zurzeit inaktiv -->

<?php echo $form->labelEx($model, 'admins'); ?>
<?php echo $form->textArea($model, 'admins', array('class' => 'span12', 'id' => 'user_select')); ?>
<?php
// build a standard dropdown list
//    echo $form->dropDownList(
//        $group, 'admins', array(), array(
//        'multiple' => true,
//        'id' => 'user_select',
//        'class' => 'user span12',
//        'data-placeholder' => Yii::t('UserModule.base', 'Add user...'),
//    ));
// attach mention widget to it
$this->widget('application.modules_core.user.widgets.UserPickerWidget', array(
    'inputId' => 'user_select',
    'maxUsers' => 2,
    // Mit diesen neuen Werten, kann man das Widget an ein Form Feld binden
    // Somit ist es in der lage, der aktuellen Wert via PHP auszulesen
    // Theoretisch könnte man sich evtl. auch damit da DropDownId Attribut sparen.
    // Müssen wir mal ausprobieren
    'model' => $model, // CForm Instanz
    'attribute' => 'admins' // Attribut davon
));
?>


<?php echo CHtml::submitButton(Yii::t('AdminModule.base', 'Save'), array('class' => 'btn btn-primary')); ?>

<?php
if (!$group->isNewRecord) {
    echo CHtml::link(Yii::t('AdminModule.base', 'Delete'), $this->createUrl('//admin/group/delete', array('id' => $group->id)), array('class' => 'btn btn-danger'));
}
?>


<?php $this->endWidget(); ?>

