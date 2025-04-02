<?php

use humhub\components\View;
use humhub\modules\ui\form\widgets\ColorPicker;
use humhub\widgets\form\ActiveForm;
use yii\base\Model;

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

<div id="<?= $containerId ?>" class="mb-3 space-color-chooser-edit" style="margin-top: 5px;">
    <?= ColorPicker::widget(['model' => $model, 'field' => 'color', 'container' => $containerId]); ?>

    <?= $form->field($model, 'name', ['template' => '
            {label}
            <div class="input-group">
                <span class="input-group-text">
                    <i></i>
                </span>
                {input}
                {error}{hint}
            </div>',
    ])->textInput(array_merge([
        'placeholder' => Yii::t('SpaceModule.manage', 'Space name'),
        'maxlength' => 45,
    ], ($focus ? ['autofocus' => ''] : []))); ?>
</div>
