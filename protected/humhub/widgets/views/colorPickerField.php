<?php

use yii\helpers\Html;

humhub\assets\HumHubColorPickerAsset::register($this);
?>

<?= Html::activeTextInput($model, $field, ['class' => 'form-control', 'id' => $inputId, 'value' => $model->color, 'style' => 'display:none']); ?>

<script type="text/javascript">
    $(function() {
        humhub.modules.ui.colorpicker.apply('#<?= $container ?>', '#<?= $inputId ?>', '<?= $model->color ?>')
    });
</script>