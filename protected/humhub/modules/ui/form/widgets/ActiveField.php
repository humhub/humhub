<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\form\widgets;

use yii\bootstrap\Html;
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
    public function checkboxList($items, $options = [])
    {
        if (!isset($options['item'])) {
            $itemOptions = $options['itemOptions'] ?? [];
            $encode = ArrayHelper::getValue($options, 'encode', true);
            $options['item'] = function ($index, $label, $name, $checked, $value) use ($itemOptions, $encode) {
                $options = array_merge([
                    'label' => $encode ? Html::encode($label) : $label,
                    'value' => $value,
                    'id' => strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name . '-' . $value)),
                ], $itemOptions);
                return '<div class="checkbox">' . Html::checkbox($name, $checked, $options) . '</div>';
            };
        }

        return parent::checkboxList($items, $options);
    }
}
