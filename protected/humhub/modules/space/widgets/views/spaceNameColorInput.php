<?php
    $containerId = time().'space-color-chooser-edit';
    
    if($model->color == null) {
        $model->color = '#d1d1d1';
    }
?>

<div id="<?= $containerId ?>" class="form-group space-color-chooser-edit" style="margin-top: 5px;">
    <?= humhub\widgets\ColorPickerField::widget(['model' => $model, 'field' => 'color', 'container' => $containerId]); ?>

    <?= $form->field($model, 'name', ['template' => '
        {label}
        <div class="input-group">
            <span class="input-group-addon">
                <i></i>
            </span>
            {input}
        </div>
        {error}{hint}'
        ])->textInput(['placeholder' => Yii::t('SpaceModule.views_create_create', 'Space name'), 'maxlength' => 45 ]) ?>
</div>
