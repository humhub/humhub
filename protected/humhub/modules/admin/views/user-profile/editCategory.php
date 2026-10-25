<?php

use humhub\modules\user\models\ProfileFieldCategory;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\bootstrap\Link;
use humhub\widgets\ContentCard;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\form\SortOrderField;

/* @var $category ProfileFieldCategory */
?>
<?php ContentCard::begin([
    'title' => $category->isNewRecord
        ? Yii::t('AdminModule.user', 'Create new profile category')
        : Yii::t('AdminModule.user', 'Edit profile category'),
    'buttons' => Button::back(['index'], Yii::t('AdminModule.base', 'Back to overview'))->sm()->asString(),
]) ?>
<?php $form = ActiveForm::begin(['acknowledge' => true]); ?>

<?= $form->field($category, 'title') ?>
<?= $form->field($category, 'description')->textarea(['rows' => 5]) ?>
<?= $form->field($category, 'sort_order')->widget(SortOrderField::class) ?>
<?= $form->field($category, 'translation_category') ?>

<hr>

<?= Button::save()->submit() ?>

<?php if (!$category->isNewRecord && !$category->is_system): ?>
    <?= Link::danger(Yii::t('AdminModule.user', 'Delete'))
        ->post(['delete-category', 'id' => $category->id])
        ->confirm()
        ->right() ?>
<?php endif; ?>

<?php ActiveForm::end(); ?>
<?php ContentCard::end() ?>
