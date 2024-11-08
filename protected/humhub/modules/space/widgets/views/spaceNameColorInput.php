<?php

use humhub\modules\ui\view\components\View;
use humhub\widgets\ColorPickerField;
use yii\base\Model;
use yii\widgets\ActiveForm;

/**
 * @var $this View
 * @var $model Model
 * @var $form ActiveForm
 * @var $focus bool
 */

$containerId = time() . 'space-color-chooser-edit';

if ($model->color === null) {
    $model->color = '#d1d1d1';
}
?>

<div id="<?= $containerId ?>" class="form-group space-color-chooser-edit" style="margin-top: 5px;">
    <?= ColorPickerField::widget(['model' => $model, 'field' => 'color', 'container' => $containerId]); ?>

    <?= $form->field($model, 'name', ['template' => '
            {label}
            <div class="input-group">
                <span class="input-group-addon">
                    <i></i>
                </span>
                {input}
            </div>
            {error}{hint}',
    ])->textInput(array_merge([
        'placeholder' => Yii::t('SpaceModule.manage', 'Space name'),
        'maxlength' => 45,
    ], ($focus ? ['autofocus' => ''] : []))); ?>
</div>
