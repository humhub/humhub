<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\admin\models\forms\ThemeSettingsForm;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\ContentCard;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\form\ColorInput;

/**
 * @var $this View
 * @var $model ThemeSettingsForm
 */

$themeVariables = Yii::$app->view->theme->variables;

// The colours in groups (docs/ui/accordion.md): the first one open, or the one holding an error
$groups = [
    'brand' => [
        'label' => Yii::t('AdminModule.settings', 'Brand colors'),
        'colors' => ['primary', 'accent', 'secondary'],
    ],
    'status' => [
        'label' => Yii::t('AdminModule.settings', 'Status colors'),
        'colors' => ['success', 'danger', 'warning', 'info'],
    ],
    'surfaces' => [
        'label' => Yii::t('AdminModule.settings', 'Surfaces'),
        'colors' => ['body-bg', 'body-bg-dark'],
    ],
];
$open = array_key_first($groups);
foreach ($groups as $id => $group) {
    foreach ($group['colors'] as $color) {
        if ($model->hasErrors(ThemeSettingsForm::colorAttribute($color))) {
            $open = $id;
            break 2;
        }
    }
}
?>
<?php ContentCard::begin(['title' => Yii::t('AdminModule.base', 'Theme')]) ?>
<?php $form = ActiveForm::begin(['acknowledge' => true]); ?>

<?= $form->field($model, 'theme')->dropDownList($model->getThemes()) ?>

<div class="accordion" id="theme-colors">
    <?php foreach ($groups as $id => $group) : ?>
        <div class="accordion-item">
            <h3 class="accordion-header">
                <button class="accordion-button<?= $id === $open ? '' : ' collapsed' ?>" type="button"
                        data-bs-toggle="collapse" data-bs-target="#theme-colors-<?= $id ?>"
                        aria-expanded="<?= $id === $open ? 'true' : 'false' ?>" aria-controls="theme-colors-<?= $id ?>">
                    <span class="accordion-title"><?= Html::encode($group['label']) ?></span>
                </button>
            </h3>
            <div id="theme-colors-<?= $id ?>" class="accordion-collapse collapse<?= $id === $open ? ' show' : '' ?>" data-bs-parent="#theme-colors">
                <div class="accordion-body">
                    <?php foreach ($group['colors'] as $color) : ?>
                        <?= $form->field($model, ThemeSettingsForm::colorAttribute($color))->widget(ColorInput::class, [
                            'default' => $themeVariables->getThemeDefault($color),
                        ]) ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<hr>
<div class="d-flex flex-wrap gap-2">
    <?= Html::submitButton(Yii::t('AdminModule.settings', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']) ?>
    <?= Button::none(Yii::t('AdminModule.settings', 'Reset colors'))->icon('arrow-back-up')->cssClass(['btn', 'btn-ghost'])->id('theme-colors-reset')
        ->options(['hidden' => !$model->hasCustomColors()]) ?>
</div>

<?php ActiveForm::end(); ?>
<?php ContentCard::end() ?>

<script <?= Html::nonce() ?>>
    $(function () {
        var $reset = $('#theme-colors-reset');
        var inputs = document.querySelectorAll('#theme-colors input[data-color-default]');
        var hex = function (value) {
            var text = String(value || '').trim().toLowerCase();
            return /^#[0-9a-f]{3}$/.test(text) ? '#' + text[1] + text[1] + text[2] + text[2] + text[3] + text[3] : text;
        };

        // "Reset colors" only while a colour differs from the theme's own (docs/ui/buttons.md: hide, don't disable)
        $('#theme-colors').on('input change', 'input[data-color-default]', function () {
            $reset.prop('hidden', !Array.prototype.some.call(inputs, function (input) {
                return hex(input.value) !== hex(input.dataset.colorDefault);
            }));
        });

        // Every colour back to the theme's own, in the form: saved with "Save". The colour inputs follow the event
        $reset.on('click', function () {
            inputs.forEach(function (input) {
                input.value = input.dataset.colorDefault;
                input.dispatchEvent(new Event('input', {bubbles: true}));
                input.dispatchEvent(new Event('change', {bubbles: true}));
            });
            // The button has hidden itself: the focus goes on to "Save"
            $reset.closest('form').find('[type=submit]').trigger('focus');
        });
    });
</script>
