<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\form\widgets;

use yii\helpers\BaseHtml;

/**
 * A HumHub enhanced version of [[\yii\bootstrap\ActiveField]].
 *
 * @since 1.2
 * @author Luke
 */
class ActiveField extends \yii\bootstrap\ActiveField
{
    /**
     * @var bool Can be set to true in order to prevent this field from being rendered. This may be used by InputWidgets
     * or other fields responsible for custom visibility management.
     *
     * @since 1.6
     */
    public $preventRendering = false;

    /**
     * @inheritdoc
     */
    public $checkboxTemplate = "<div class=\"checkbox regular-checkbox-container\">\n{beginLabel}\n{input}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>";

    /**
     * @inheritdoc
     */
    public $radioTemplate = "<div class=\"radio regular-radio-container\">\n{beginLabel}\n{input}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>";

    /**
     * @inheritdoc
     */
    public function widget($class, $config = [])
    {
        /* @var $class \yii\base\Widget */
        $config['model'] = $this->model;
        $config['attribute'] = $this->attribute;
        $config['view'] = $this->form->getView();

        if(is_subclass_of($class, JsInputWidget::class)) {
            if(isset($config['options'])) {
                $this->adjustLabelFor($config['options']);
            }

            $config['field'] = $this;
        }

        return parent::widget($class, $config);
    }

    /**
     * @inheritdoc
     */
    public function begin()
    {
        if($this->preventRendering) {
            return '';
        }

        return parent::begin();
    }

    /**
     * @inheritdoc
     */
    public function render($content = null)
    {
        if($this->preventRendering) {
            return '';
        }

        return parent::render($content);
    }

    /**
     * @inheritdoc
     */
    public function end()
    {
        if($this->preventRendering) {
            return '';
        }

        return parent::end();
    }

    /**
     * @inheritdoc
     */
    public function checkbox($options = [], $enclosedByLabel = true)
    {
        return parent::checkbox($this->applyRegularSingleStyle($options, [
            'inputClass' => 'regular-checkbox',
            'boxClass' => 'regular-checkbox-box',
            'template' => $this->checkboxTemplate,
        ]), $enclosedByLabel);
    }

    /**
     * @inheritdoc
     */
    public function checkboxList($items, $options = [])
    {
        return parent::checkboxList($items, $options)->applyRegularListStyle([
            'type' => 'checkbox',
            'containerFindClass' => 'checkbox',
            'containerAdditionalClass' => 'regular-checkbox-container',
            'inputClass' => 'regular-checkbox',
            'boxClass' => 'regular-checkbox-box',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function radio($options = [], $enclosedByLabel = true)
    {
        return parent::radio($this->applyRegularSingleStyle($options, [
            'inputClass' => 'regular-radio',
            'boxClass' => 'regular-radio-button',
            'template' => $this->radioTemplate,
        ]), $enclosedByLabel);
    }

    /**
     * @inheritdoc
     */
    public function radioList($items, $options = [])
    {
        return parent::radioList($items, $options)->applyRegularListStyle([
            'type' => 'radio',
            'containerFindClass' => 'radio',
            'containerAdditionalClass' => 'regular-radio-container',
            'inputClass' => 'regular-radio',
            'boxClass' => 'regular-radio-button',
        ]);
    }

    /**
     * Apply style "regular" for single of checkbox or radio input
     *
     * @param array $options
     * @param array $regularOptions
     * @return array
     */
    private function applyRegularSingleStyle(array $options, array $regularOptions): array
    {
        $options['class'] = isset($options['class']) ? $options['class'] . $regularOptions['inputClass'] : $regularOptions['inputClass'];

        if (!isset($options['template']) && isset($regularOptions['template'])) {
            $endLabel = '{endLabel}';
            $options['template'] = str_replace($endLabel, $this->getRegularBox(array_merge($options, $regularOptions)) . $endLabel, $regularOptions['template']);
        }

        return $options;
    }

    /**
     * Apply style "regular" for list of checkbox or radio inputs
     *
     * @param array $options
     * @return $this
     */
    private function applyRegularListStyle(array $options): self
    {
        if (!isset($this->parts['{input}'])) {
            return $this;
        }

        $regexp = '~(<div class="' . $options['containerFindClass'] . '")(.*?>[\r\n]*<label.*?>[\r\n]*<input type="' . $options['type'] . '")(.+?)(/?>.+?)(</label>[\r\n]*</div>)~im';

        $this->parts['{input}'] = preg_replace_callback($regexp, function ($html) use($options) {
            $regularOptions = [];

            $inputAttrs = $html[3];
            if (preg_match_all('/([a-z\-_]+)(="(.*?)")?/i', $inputAttrs, $m)) {
                $attrs = array_combine($m[1], $m[3]);
                foreach ($attrs as $attr => $value) {
                    if ($value === '') {
                        $attrs[$attr] = true;
                    }
                }
                $attrs['class'] = (isset($attrs['class']) ? $attrs['class'] . ' ' : '') . $options['inputClass'];
                $inputAttrs = BaseHtml::renderTagAttributes($attrs);

                if (isset($options['boxClass'])) {
                    $regularOptions['boxClass'] = $options['boxClass'];
                }
                if (isset($attrs['disabled'])) {
                    $regularOptions['disabled'] = true;
                }
                if (isset($attrs['style'])) {
                    $regularOptions['style'] = $attrs['style'];
                }
            }

            return '<div class="' . $options['containerFindClass'] . ' ' . $options['containerAdditionalClass'] . '"'
                . $html[2] // <label><input type=...
                . $inputAttrs
                . $html[4] // ...> Label text
                . $this->getRegularBox($regularOptions)
                . $html[5]; // </label></div>
        }, $this->parts['{input}']);

        return $this;
    }

    /**
     * HTML code to checkbox/radio for style "regular"
     *
     * @param array $options
     * @return string
     */
    private function getRegularBox(array $options = []): string
    {
        $checkboxOptions = [];
        $checkboxOptions['class'] = $options['boxClass'] ?? 'regular-checkbox-box';
        if (!empty($options['disabled'])) {
            $checkboxOptions['class'] .= ' disabled';
        }
        if (isset($options['style'])) {
            $checkboxOptions['style'] = $options['style'];
        }

        return BaseHtml::tag('div', '', $checkboxOptions);
    }
}
