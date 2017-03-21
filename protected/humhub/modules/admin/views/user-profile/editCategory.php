<?php

use humhub\compat\CActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;
?>
<div class="panel-body">
    <div class="pull-right">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i>&nbsp;&nbsp;' . Yii::t('AdminModule.user', 'Back to overview'), Url::to(['index']), array('class' => 'btn btn-default')); ?>
    </div>   

    <?php if (!$category->isNewRecord) : ?>
        <h4><?= Yii::t('AdminModule.views_userprofile_editCategory', 'Edit profile category'); ?></h4>
    <?php else: ?>
        <h4><?= Yii::t('AdminModule.views_userprofile_editCategory', 'Create new profile category'); ?></h4>
    <?php endif; ?>
    <br>

    <?php $form = CActiveForm::begin(); ?>

    <div class="form-group">
        <?= $form->labelEx($category, 'title') ?>
        <?= $form->textField($category, 'title', array('class' => 'form-control')); ?>
        <?= $form->error($category, 'title'); ?>
    </div>

    <div class="form-group">
        <?= $form->labelEx($category, 'description') ?>
        <?= $form->textArea($category, 'description', array('class' => 'form-control', 'rows' => '5')); ?>
        <?= $form->error($category, 'description'); ?>
    </div>

    <div class="form-group">
        <?= $form->labelEx($category, 'sort_order') ?>
        <?= $form->textField($category, 'sort_order', array('class' => 'form-control')); ?>
        <?= $form->error($category, 'sort_order'); ?>
    </div>

    <div class="form-group">
        <?= $form->labelEx($category, 'translation_category') ?>
        <?= $form->textField($category, 'translation_category', array('class' => 'form-control')); ?>
        <?= $form->error($category, 'translation_category'); ?>
    </div>

    <hr>

    <?= Html::submitButton(Yii::t('AdminModule.views_userprofile_editCategory', 'Save'), array('class' => 'btn btn-primary', 'data-ui-loader' => "")); ?>

    <?php if (!$category->isNewRecord && !$category->is_system) : ?>
        <?= Html::a(Yii::t('AdminModule.views_userprofile_editCategory', 'Delete'), Url::to(['delete-category', 'id' => $category->id]), array('class' => 'btn btn-danger')); ?>
    <?php endif; ?>

    <?php CActiveForm::end(); ?>
</div>