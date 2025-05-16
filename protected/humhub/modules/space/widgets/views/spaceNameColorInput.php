<?php

use humhub\components\View;
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

<label class="form-label" for="<?= $containerId ?>"><?= $model->attributeLabels()['name'] ?></label>

<div id="<?= $containerId ?>" class="mb-3 input-group space-color-chooser-edit">

    <div class="input-group-prepend">
        <?= $form->field($model, 'color', ['options' => []])
            ->colorInput(['style' => 'border-top-right-radius: 0; border-bottom-right-radius: 0; height: 36px;'])
            ->label(false) ?>
    </div>

    <?= $form->field($model, 'name', ['options' => ['class' => 'flex-grow-1']])
        ->textInput(array_merge(
            [
                'placeholder' => Yii::t('SpaceModule.manage', 'Space name'),
                'maxlength' => 45,
                'style' => 'margin-left: calc(-1 * var(--bs-border-width)); border-top-left-radius: 0; border-bottom-left-radius: 0; height: 36px;',
            ],
            ($focus ? ['autofocus' => ''] : []),
        ))
        ->label(false) ?>
</div>
