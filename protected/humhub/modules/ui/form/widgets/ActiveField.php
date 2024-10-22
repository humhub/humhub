<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\form\widgets;

use humhub\libs\Html;
use humhub\modules\ui\icon\widgets\Icon;
use yii\base\Widget;
use yii\helpers\ArrayHelper;

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

        return parent::widget($class, $config);
    }

    /**
     * @inheritdoc
     */
    public function begin()
    {
        if ($this->preventRendering) {
            return '';
        }

        return parent::begin();
    }

    /**
     * @inheritdoc
     */
    public function render($content = null)
    {
        if ($this->preventRendering) {
            return '';
        }

        return parent::render($content);
    }

    /**
     * @inheritdoc
     */
    public function end()
    {
        if ($this->preventRendering) {
            return '';
        }

        return parent::end();
    }

    /**
     * Override drop-down list to enable plugin Select2 with
     *     searchable feature if items >= $options['minimumResultsForSearch'],
     *     -1 - to never display the search box,
     *      0 - always display the search box.
     * @inheritdoc
     */
    public function dropDownList($items, $options = [])
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
    public function radioList($items, $options = [])
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
}
