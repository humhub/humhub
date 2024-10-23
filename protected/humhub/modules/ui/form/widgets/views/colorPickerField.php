<?php

use humhub\helpers\Html;
use yii\base\Model;

humhub\assets\HumHubColorPickerAsset::register($this);

/* @var $model Model */
/* @var $field string */
/* @var $inputId string */
/* @var $container string */

?>

<?= Html::activeTextInput($model, $field, ['class' => 'form-control', 'id' => $inputId, 'value' => $model->$field, 'style' => 'display:none']); ?>

<script <?= \humhub\helpers\Html::nonce() ?>>
    $(function () {
        humhub.modules.ui.colorpicker.apply('#<?= $container ?>', '#<?= $inputId ?>', '<?= $model->$field ?>')
    });
</script>
