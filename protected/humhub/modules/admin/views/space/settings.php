<?php

use humhub\libs\Html;
use humhub\widgets\Button;
use yii\bootstrap\ActiveForm;

/* @var $model \humhub\modules\admin\models\forms\SpaceSettingsForm */
/* @var $joinPolicyOptions array */
/* @var $visibilityOptions array */
/* @var $contentVisibilityOptions array */

?>
<h4><?= Yii::t('AdminModule.space', 'Space Settings'); ?></h4>
<div class="help-block">
    <?= Yii::t('AdminModule.space', 'Here you can define your default settings for new spaces. These settings can be overwritten for each individual space.'); ?>
</div>

<?php $form = ActiveForm::begin(['id' => 'space-settings-form']); ?>

<?= humhub\modules\space\widgets\SpacePickerField::widget([
    'form' => $form,
    'model' => $model,
    'attribute' => 'defaultSpaceGuid',
    'selection' => $model->defaultSpaces
])?>

<?= $form->field($model, 'defaultVisibility')->dropDownList($visibilityOptions) ?>

<?= $form->field($model, 'defaultJoinPolicy')->dropDownList($joinPolicyOptions, ['disabled' => $model->defaultVisibility == 0]) ?>

<?= $form->field($model, 'defaultContentVisibility')->dropDownList($contentVisibilityOptions, ['disabled' => $model->defaultVisibility == 0]) ?>

<?= Button::primary(Yii::t('base', 'Save'))->submit(); ?>

<?php ActiveForm::end(); ?>

<?= Html::beginTag('script'); ?>
    $('#spacesettingsform-defaultvisibility').on('change', function () {
        if (this.value == 0) {
            $('#spacesettingsform-defaultjoinpolicy, #spacesettingsform-defaultcontentvisibility').val('0').prop('disabled', true);
        } else {
            $('#spacesettingsform-defaultjoinpolicy, #spacesettingsform-defaultcontentvisibility').val('0').prop('disabled', false);
        }
    });
<?= Html::endTag('script'); ?>
