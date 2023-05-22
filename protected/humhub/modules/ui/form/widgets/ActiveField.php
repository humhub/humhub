<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\form\widgets;

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
}
