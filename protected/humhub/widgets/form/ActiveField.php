<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets\form;

use humhub\helpers\Html;
use humhub\widgets\Icon;
use yii\base\Widget;
use yii\helpers\ArrayHelper;

/**
 * A HumHub enhanced version of native bootstrap ActiveField
 *
 * @since 1.2
 * @author Luke
 */
class ActiveField extends \yii\bootstrap5\ActiveField
{
    /**
     * @inheritdoc
     * @var ActiveForm
     */
    public $form;

    /**
     * @var bool Can be set to true in order to prevent this field from being rendered. This may be used by InputWidgets
     * or other fields responsible for custom visibility management.
     *
     * @since 1.6
     */
    public $preventRendering = false;

    /**
     * @var array|false|null the arguments of the last `hint()` call (`[content, options]`), `false` for no hint, `null`
     * when it was not called: the hint is rendered again with its id once the input is known, see `render()`
     */
    private array|false|null $hintArguments = null;

    public function isPreventRendering(): bool
    {
        return $this->preventRendering
            || (
                $this->form->renderOnlySafeAttributes
                && !$this->model->isAttributeSafe($this->attribute)
                && empty($this->inputOptions['readonly'])
            );
    }

    /**
     * @inheritdoc
     */
    public function widget($class, $config = [])
    {
        /* @var $class Widget */
        $config['model'] = $this->model;
        $config['attribute'] = $this->attribute;
        $config['view'] = $this->form->getView();

        if (is_subclass_of($class, JsInputWidget::class)) {
            if (isset($config['options'])) {
                $this->adjustLabelFor($config['options']);
            }

            $config['field'] = $this;
        }

        $this->setReadOnlyFromOptions($config);

        return parent::widget($class, $config);
    }

    /**
     * @inheritdoc
     */
    public function begin()
    {
        return $this->isPreventRendering() ? '' : parent::begin();
    }

    /**
     * @inheritdoc
     */
    public function render($content = null): string
    {
        if ($this->isPreventRendering()) {
            return '';
        }

        if ($content === null) {
            if (!isset($this->parts['{input}'])) {
                $this->textInput();
            }
            $this->addHintDescription();
        }

        return parent::render($content);
    }

    /**
     * @inheritdoc
     */
    public function hint($content, $options = [])
    {
        $this->hintArguments = $content === false ? false : [$content, $options];

        return parent::hint($content, $options);
    }

    /**
     * @inheritdoc
     */
    public function end()
    {
        return $this->isPreventRendering() ? '' : parent::end();
    }

    /**
     * Override drop-down list to enable the picker (`ui.picker`) with
     *     searchable feature if items >= $options['minimumResultsForSearch'],
     *     -1 - to never display the search box,
     *      0 - always display the search box.
     * @inheritdoc
     */
    public function dropDownList($items, $options = []): self
    {
        return parent::dropDownList($items, Html::getDropDownListOptions($options));
    }

    /**
     * Use option 'template' = 'pills' to stylize radio inputs to pills
     * Other options for the template:
     *  - 'wide' = true to make it wide to full width
     *  - 'activeIcon' - Icon for an active radio item (only if the item has no icon)
     *
     * @inheritdoc
     */
    public function radioList($items, $options = []): self
    {
        if (isset($options['template']) && $options['template'] === 'pills') {
            unset($options['template']);
            $this->label(false);
            Html::addCssClass($options, 'radio-pills');
            if (isset($options['wide'])) {
                if ($options['wide']) {
                    Html::addCssClass($options, 'radio-pills-wide');
                }
                unset($options['wide']);
            }

            $itemOptions = $options['itemOptions'] ?? [];
            $encode = ArrayHelper::getValue($options, 'encode', true);
            if (isset($options['activeIcon'])) {
                $activeIcon = $options['activeIcon'];
                unset($options['activeIcon']);
            } else {
                $activeIcon = 'check-circle';
            }

            $options['item'] = function ($index, $label, $name, $checked, $value) use ($itemOptions, $encode, $activeIcon) {
                if (!$checked && empty($value) && empty($this->model->{$this->attribute})) {
                    $checked = true;
                }

                $icon = null;
                if (is_array($label)) {
                    $icon = $label['icon'] ?? $label[0] ?? null;
                    $label = $label['label'] ?? $label[1] ?? '';
                }
                if (empty($icon)) {
                    $icon = Icon::get($activeIcon)->class('radio-pill-active-icon');
                }

                $options = array_merge([
                    'label' => ($icon ? Icon::get($icon) : '') . ($encode ? Html::encode($label) : $label),
                    'value' => $value,
                ], $itemOptions);
                return '<div class="radio' . ($checked ? ' active' : '') . '">'
                    . Html::radio($name, $checked, $options)
                    . '</div>';
            };
        }

        return parent::radioList($items, $options);
    }

    /**
     * @inheritdoc
     */
    protected function addAriaAttributes(&$options)
    {
        $this->setReadOnlyFromOptions($options);
        parent::addAriaAttributes($options);
        $this->addErrorDescription($options);
    }

    /**
     * The error message describes the input (WCAG 3.3.1). Its container is rendered without a message too, so one the
     * client validation adds later is read as well.
     *
     * @param array $options the input options
     * @since 1.20
     */
    protected function addErrorDescription(array &$options): void
    {
        if (!$this->addAriaAttributes || $this->enableError === false || !is_array($this->errorOptions)
            || ($this->errorOptions['tag'] ?? 'div') === false || !str_contains($this->template, '{error}')) {
            return;
        }

        $inputId = $options['id'] ?? $this->getInputId();
        if (empty($inputId)) {
            return;
        }

        $this->errorOptions['id'] ??= $inputId . '-error';
        $describedBy = preg_split('/\s+/', trim(($options['aria-describedby'] ?? '') . ' ' . $this->errorOptions['id']));
        $options['aria-describedby'] = implode(' ', array_unique($describedBy));
    }

    /**
     * The hint describes the input, before its error message (WCAG 1.3.1, 3.3.2), as in the Vue form fields. A hint is
     * often set after the input (`->textInput()->hint()`), so the input is rendered already: the hint gets its id
     * `{input id}-hint` here, and the rendered input the reference.
     *
     * @since 1.20
     */
    protected function addHintDescription(): void
    {
        if (!$this->addAriaAttributes || $this->hintArguments === false || !str_contains($this->template, '{hint}')) {
            return;
        }

        $inputId = $this->getInputId();
        if (empty($inputId)) {
            return;
        }

        [$content, $options] = $this->hintArguments ?? [null, []];
        $options['id'] ??= $this->hintOptions['id'] ?? $inputId . '-hint';
        parent::hint($content, $options);
        if ($this->parts['{hint}'] === '') {
            return;
        }

        // The opening tag of the input: the element of the rendered input that carries its id
        $input = $this->parts['{input}'];
        $idAttribute = 'id="' . Html::encode($inputId) . '"';
        if (!preg_match('/<[a-z][^>]*\s' . preg_quote($idAttribute, '/') . '[^>]*>/i', $input, $match, PREG_OFFSET_CAPTURE)) {
            return;
        }

        [$tag, $offset] = $match[0];
        $hintId = Html::encode($options['id']);
        if (preg_match('/\saria-describedby="([^"]*)"/', $tag, $describedBy)) {
            $ids = array_unique(array_merge([$hintId], preg_split('/\s+/', trim($describedBy[1]))));
            $newTag = str_replace($describedBy[0], ' aria-describedby="' . implode(' ', $ids) . '"', $tag);
        } else {
            $newTag = preg_replace('/\s*(\/?)>$/', ' aria-describedby="' . $hintId . '"$1>', $tag, 1);
        }

        $this->parts['{input}'] = substr_replace($input, $newTag, $offset, strlen($tag));
    }

    /**
     * Set this active field as read only when this flag is set after the field has been already initialized.
     * Directly calling, e.g., `textInput($inputOptions)` does not merge the options into `ActiveField::$inputOptions`.
     * Since the `readonly` option is needed later for `$preventRendering`, the option is intercepted here.
     *
     * @param array $options
     * @return void
     */
    private function setReadOnlyFromOptions(array $options): void
    {
        if (isset($options['readonly']) || isset($options['disabled'])) {
            $this->inputOptions['readonly'] = $options['readonly'] ?? $options['disabled'];
        }
    }
}
