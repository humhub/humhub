<?php

use yii\helpers\Html;

humhub\assets\HumHubColorPickerAsset::register($this);

/* @var $model \yii\base\Model*/
/* @var $field string */
/* @var $inputId string */
/* @var $container string */

?>

<?= Html::activeTextInput($model, $field, ['class' => 'form-control', 'id' => $inputId, 'value' => $model->$field, 'style' => 'display:none']); ?>

<script type="text/javascript">
    $(function() {
        humhub.modules.ui.colorpicker.apply('#<?= $container ?>', '#<?= $inputId ?>', '<?= $model->$field ?>')
    });
</script>
