<?php

use humhub\compat\CActiveForm;
use yii\helpers\Url;
use humhub\libs\Html;
?>
<div class="panel-body">
    <div class="pull-right">
        <?= Html::backButton(['index'], ['label' => Yii::t('AdminModule.base', 'Back to overview'), 'class' => 'pull-right']); ?>
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
        <?= $form->textField($category, 'title', ['class' => 'form-control']); ?>
        <?= $form->error($category, 'title'); ?>
    </div>

    <div class="form-group">
        <?= $form->labelEx($category, 'description') ?>
        <?= $form->textArea($category, 'description', ['class' => 'form-control', 'rows' => '5']); ?>
        <?= $form->error($category, 'description'); ?>
    </div>

    <div class="form-group">
        <?= $form->labelEx($category, 'sort_order') ?>
        <?= $form->textField($category, 'sort_order', ['class' => 'form-control']); ?>
        <?= $form->error($category, 'sort_order'); ?>
    </div>

    <div class="form-group">
        <?= $form->labelEx($category, 'translation_category') ?>
        <?= $form->textField($category, 'translation_category', ['class' => 'form-control']); ?>
        <?= $form->error($category, 'translation_category'); ?>
    </div>

    <hr>

    <?= Html::submitButton(Yii::t('AdminModule.views_userprofile_editCategory', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>

    <?php if (!$category->isNewRecord && !$category->is_system) : ?>
        <?= Html::a(Yii::t('AdminModule.views_userprofile_editCategory', 'Delete'), Url::to(['delete-category', 'id' => $category->id]), ['class' => 'btn btn-danger']); ?>
    <?php endif; ?>

    <?php CActiveForm::end(); ?>
</div>