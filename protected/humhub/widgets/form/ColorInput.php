<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets\form;

use humhub\helpers\Html;
use humhub\widgets\VueComponent;
use yii\widgets\InputWidget;

/**
 * A colour as a hex value with the browser's own colour picker: a text field for `#rrggbb` and, as its swatch, a
 * native `<input type="color">`. The `ColorInput` Vue component (`vue/ColorInput.vue`) mounts over the native text
 * input rendered here, which keeps the name and carries the value; without JavaScript the text input stays usable.
 *
 * ```php
 * $form->field($model, 'color')->widget(ColorInput::class, ['default' => '#435f6f'])
 * ```
 *
 * @since 1.20
 */
class ColorInput extends InputWidget
{
    /**
     * @var string|null the colour a reset returns to, as `data-color-default` of the input (see the theme settings)
     */
    public ?string $default = null;

    /**
     * @inheritdoc
     */
    public function run(): string
    {
        Html::addCssClass($this->options, 'form-control');
        $this->options['maxlength'] ??= 7;
        $this->options['spellcheck'] = 'false';
        $this->options['autocomplete'] = 'off';
        if ($this->default !== null) {
            $this->options['data-color-default'] = $this->default;
        }

        $input = $this->hasModel()
            ? Html::activeTextInput($this->model, $this->attribute, $this->options)
            : Html::textInput($this->name, $this->value, $this->options);

        return $input . VueComponent::widget([
            'name' => 'ColorInput',
            'props' => ['inputId' => $this->options['id']],
            'options' => ['class' => 'color-input-host'],
        ]);
    }
}
