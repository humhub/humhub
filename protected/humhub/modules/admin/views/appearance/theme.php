<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\admin\models\forms\ThemeSettingsForm;
use humhub\widgets\ContentCard;
use humhub\widgets\form\ActiveForm;

/**
 * @var $this View
 * @var $model ThemeSettingsForm
 */

$themeVariables = Yii::$app->view->theme->variables;
$checkBoxOptions = ['options' => ['class' => 'pt-2']];
?>
<?php ContentCard::begin(['title' => Yii::t('AdminModule.base', 'Theme')]) ?>
<?php $form = ActiveForm::begin(['acknowledge' => true]); ?>

<?= $form->field($model, 'theme')->dropDownList($model->getThemes()) ?>

<div class="container gx-0 overflow-x-hidden">
    <div class="row">
        <?php foreach (ThemeSettingsForm::COLORS as $color) : ?>
            <?php $attribute = ThemeSettingsForm::colorAttribute($color) ?>
            <?php $default = ThemeSettingsForm::defaultAttribute($color) ?>
            <div class="col-lg-4 mb-3">
                <?= Html::activeLabel($model, $attribute) ?>
                <div class="input-group input-color-group bg-light p-3 pb-0">
                    <?= $form->field($model, $attribute)->hint(false)->colorInput([
                        'disabled' => $model->$default,
                        'class' => 'rounded',
                        'data-default-color' => $themeVariables->get($color),
                    ]) ?>
                    <?= $form->field($model, $default, $checkBoxOptions)->checkbox([
                        'data-color-input' => Html::getInputId($model, $attribute),
                    ]) ?>
                </div>
                <?php if ($model->getAttributeHint($attribute) !== '') : ?>
                    <div class="form-text"><?= Html::encode($model->getAttributeHint($attribute)) ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<hr>
<?= Html::submitButton(Yii::t('AdminModule.settings', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']) ?>

<?php ActiveForm::end(); ?>
<?php ContentCard::end() ?>

<script <?= Html::nonce() ?>>
    $(function () {
        // Disable the colour field while its "Default" checkbox is checked
        $('[data-color-input]').on('change', function () {
            const $colorField = $('#' + $(this).data('color-input'));
            if (this.checked) {
                $colorField.prop('disabled', true).prop('value', $colorField.data('default-color'));
            } else {
                $colorField.prop('disabled', false);
            }
        });
    })
</script>
