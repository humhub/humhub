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

    <h4><?= Yii::t('AdminModule.user', 'People'); ?></h4>
    <div class="help-block">
        <?= Yii::t('AdminModule.user', 'Select which user information should be displayed in the \'People\' overview. You can select any profile fields, even those you have created individually. '); ?>
    </div>

    <br />

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'detail1')->dropDownList($model->getDetailOptions()); ?>
    <?= $form->field($model, 'detail2')->dropDownList($model->getDetailOptions()); ?>
    <?= $form->field($model, 'detail3')->dropDownList($model->getDetailOptions()); ?>

    <?= $form->field($model, 'defaultSorting')->dropDownList(PeopleSettingsForm::getSortingOptions()); ?>
    <div id="defaultSortingGroupSelector"<?php if ($model->defaultSorting !== '') : ?> style="display:none"<?php endif; ?>>
        <?= $form->field($model, 'defaultSortingGroup')->dropDownList(PeopleSettingsForm::getSortingGroupOptions()); ?>
    </div>

    <?= Button::save(Yii::t('AdminModule.user', 'Save'))->submit(); ?>

    <?php ActiveForm::end(); ?>
</div>
<script <?= Html::nonce() ?>>
$('select[name="PeopleSettingsForm[defaultSorting]"]').change(function(){
    $('#defaultSortingGroupSelector').toggle($(this).val() === '');
})
</script>