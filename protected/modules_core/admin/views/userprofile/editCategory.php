<div class="panel panel-default">

    <?php if (!$category->isNewRecord) : ?>
        <div
            class="panel-heading"><?php echo Yii::t('AdminModule.views_userprofile_editCategory', '<strong>Edit</strong> profile category'); ?></div>
    <?php else: ?>
        <div
            class="panel-heading"><?php echo Yii::t('AdminModule.views_userprofile_editCategory', '<strong>Create</strong> new profile category'); ?></div>
    <?php endif; ?>

    <div class="panel-body">

        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'admin-userprofile-editcategory',
            'enableAjaxValidation' => false,
        ));
        ?>

        <div class="form-group">
            <?php echo $form->labelEx($category, 'title') ?>
            <?php echo $form->textField($category, 'title', array('class' => 'form-control')); ?>
            <?php echo $form->error($category, 'title'); ?>
        </div>

        <div class="form-group">
            <?php echo $form->labelEx($category, 'description') ?>
            <?php echo $form->textArea($category, 'description', array('class' => 'form-control', 'rows' => '5')); ?>
            <?php echo $form->error($category, 'title'); ?>
        </div>

        <div class="form-group">
            <?php echo $form->labelEx($category, 'sort_order') ?>
            <?php echo $form->textField($category, 'sort_order', array('class' => 'form-control')); ?>
            <?php echo $form->error($category, 'title'); ?>
        </div>

        <div class="form-group">
            <?php echo $form->labelEx($category, 'translation_category') ?>
            <?php echo $form->textField($category, 'translation_category', array('class' => 'form-control')); ?>
            <?php echo $form->error($category, 'translation_category'); ?>
        </div>

        <hr>

        <?php echo CHtml::submitButton(Yii::t('AdminModule.views_userprofile_editCategory', 'Save'), array('class' => 'btn btn-primary')); ?>

        <?php if (!$category->isNewRecord && !$category->is_system) : ?>
            <?php echo HHtml::postLink(Yii::t('AdminModule.views_userprofile_editCategory', 'Delete'), $this->createUrl('//admin/userprofile/deleteCategory', array('id' => $category->id)), array('class' => 'btn btn-danger')); ?>
        <?php endif; ?>

        <?php $this->endWidget(); ?>

    </div>
</div>