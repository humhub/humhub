<?php

use yii\helpers\Html;

humhub\assets\HumHubColorPicker::register($this);
?>

<?= Html::activeTextInput($model, $field, ['class' => 'form-control', 'id' => $inputId, 'value' => $model->color, 'style' => 'display:none']); ?>

<script type="text/javascript">
    humhub.modules.ui.colorpicker.apply('#<?= $container ?>', '#<?= $inputId ?>', '<?= $model->color ?>');
</script>