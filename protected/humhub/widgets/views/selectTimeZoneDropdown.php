<?php
use humhub\libs\Html;
use humhub\widgets\Link;
?>
<?= Link::withAction($currentTimeZoneLabel, 'ui.form.elements.toggleTimeZoneInput')->cssClass($toggleClass)->cssClass('timeZoneToggle') ?>
<div class="timeZoneInputContainer" style="display:none">
    <?= Html::label(Yii::t('base', 'Time Zone'), $id, ['class' => 'control-label'])?>
    <?php if($model) : ?>
        <?= Html::activeDropDownList($model, $attribute, $timeZoneItems, ['id' => $id, 'data-action-change' => 'ui.form.elements.timeZoneSelected', 'data-ui-select2' => '', 'style' => 'width:100%']) ?>
    <?php elseif($name) : ?>
        <?= Html::dropDownList($name, $value, $timeZoneItems, ['data-action-change' => 'ui.form.elements.timeZoneSelected', 'data-ui-select2' => '', 'style' => 'width:100%']) ?>
    <?php endif; ?>
</div>