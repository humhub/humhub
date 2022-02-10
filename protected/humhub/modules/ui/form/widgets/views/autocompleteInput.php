<?php

use yii\helpers\Html;

humhub\assets\AutocompleteInputAsset::register($this);

/* @var $form ActiveForm */
/* @var $model Model */
/* @var $field string */
/* @var $options string */

?>

<?php if (!empty($form)): ?>

    <?= $form->field($model, $field)->textInput($options) ?>

<?php elseif(!empty($model)): ?>

    <?= Html::activeTextInput($model, $field, $options); ?>

<?php else: ?>

    <?= Html::textInput($field, '', $options); ?>

<?php endif; ?>
