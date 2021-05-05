<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
use humhub\modules\admin\models\forms\PeopleSettingsForm;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\widgets\Button;

/* @var $model PeopleSettingsForm */
?>

<div class="panel-body">

    <h4><?= Yii::t('AdminModule.user', 'People Configuration'); ?></h4>
    <div class="help-block">
        <?= Yii::t('AdminModule.user', 'Here you can configurate the people page view.'); ?>
    </div>

    <br />

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'userDetails')->dropDownList($model->getUserDetailsOptions()); ?>

    <div class="fields-people-settings-backside-lines"<?php if ($model->userDetails == 'front') : ?> style="display:none"<?php endif; ?>>
        <?= $form->field($model, 'backsideLine1')->dropDownList($model->getBacksideLineOptions()); ?>
        <?= $form->field($model, 'backsideLine2')->dropDownList($model->getBacksideLineOptions()); ?>
        <?= $form->field($model, 'backsideLine3')->dropDownList($model->getBacksideLineOptions()); ?>
    </div>

    <?= $form->field($model, 'defaultSorting')->dropDownList(PeopleSettingsForm::getSortingOptions()); ?>

    <?= Button::save(Yii::t('AdminModule.user', 'Save'))->submit(); ?>

    <?php ActiveForm::end(); ?>
</div>
<script <?= Html::nonce() ?>>
$('select[name="PeopleSettingsForm[userDetails]"]').change(function() {
    $('.fields-people-settings-backside-lines').toggle($(this).val() !== 'front');
});
</script>