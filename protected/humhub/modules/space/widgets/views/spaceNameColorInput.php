<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\space\models\Space;
use humhub\widgets\form\ActiveForm;

/**
 * @var $this View
 * @var $model Space
 * @var $form ActiveForm
 * @var $focus bool
 */

if ($model->color === null) {
    $model->color = '#d1d1d1';
}
?>

<?= Html::activeLabel($model, 'name') ?>
<div class="input-group input-color-group">
    <?= $form->field($model, 'color')->colorInput() ?>
    <?= $form->field($model, 'name')
        ->textInput(array_merge(
            [
                'placeholder' => Yii::t('SpaceModule.manage', 'Space name'),
                'maxlength' => 45,
            ],
            ($focus ? ['autofocus' => ''] : []),
        )) ?>
</div>
