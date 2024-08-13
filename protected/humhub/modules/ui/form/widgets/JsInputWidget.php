<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\form\widgets;

use humhub\widgets\JsWidget;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\Html;
use yii\widgets\InputWidget;

/**
 * InputWidget is the base class for widgets that collect user inputs.
 *
 * An input widget can be associated with a data model and an attribute,
 * or a name and a value. If the former, the name and the value will
 * be generated automatically.
 *
 * Classes extending from this widget can be used in an [[\yii\widgets\ActiveForm|ActiveForm]]
 * using the [[\yii\widgets\ActiveField::widget()|widget()]] method, for example like this:
 *
 * ```php
 * <?= $form->field($model, 'from_date')->widget('WidgetClassName', [
 *     // configure additional widget properties here
 * ]) ?>
 * ```
 *
 * For more details and usage information on InputWidget, see the [guide article on forms](guide:input-forms).
 *
 * @see InputWidget
 * @author Luke
 * @since 1.3
 */
abstract class JsInputWidget extends JsWidget
{
    /**
     * If the ActiveForm is set, it should be used to create input field.
     * This may differ between implementations.
     *
     * @var \yii\widgets\ActiveForm
     */
    public $form;

    /**
     * @var Model the data model that this widget is associated with.
     */
    public $model;

    /**
     * @var string the model attribute that this widget is associated with.
     */
    public $attribute;

    /**
     * @var string the input name. This must be set if [[model]] and [[attribute]] are not set.
     */
    public $name;

    /**
     * @var string the input value.
     */
    public $value;

    /**
     * @var array the HTML attributes for the input tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = [];

    /**
     * @var \yii\widgets\ActiveField active input field, which triggers this widget rendering.
     * This field will be automatically filled up in case widget instance is created via [[\yii\widgets\ActiveField::widget()]].
     * @since 1.6
     */
    public $field;

    /**
     * Initializes the widget.
     * If you override this method, make sure you call the parent implementation first.
     */
    public function beforeRun()
    {
        if (!parent::beforeRun()) {
            return false;
        }
        if ($this->name === null && !$this->hasModel()) {
            throw new InvalidConfigException("Either 'name', or 'model' and 'attribute' properties must be specified.");
        }
        if (!$this->id && !isset($this->options['id'])) {
            $this->id = $this->options['id'] = $this->hasModel() ? Html::getInputId($this->model, $this->attribute) : $this->getId(true);
        }

        return true;
    }

    /**
     * Should be returned by [[run]] in order to prevent rendering the field.
     * This function will prepare the ActiveField instance by resetting the template and label and return
     * an empty string.
     *
     * ```php
     * public function run()
     * {
     *   if(!$this->shouldRender()) {
     *       return $this->emptyResult();
     *   }
     *
     *   return parent::run();
     * }
     * ```
     * @return string
     * @since 1.6
     */
    protected function emptyResult()
    {
        if($this->field) {
            $this->field->label(false);
            // Prevents empty-help/error block rendering
            $this->field->template = '';

            if($this->field instanceof ActiveField) {
                $this->field->preventRendering = true;
            }
        }

        return '';
    }

    /**
     * @return string the field value either by extracting from model or if no model is given `$this->value`
     * @since 1.3
     */
    protected function getValue()
    {
        if ($this->hasModel()) {
            return Html::getAttributeValue($this->model, $this->attribute);
        }

        return $this->value;
    }

    /**
     * @return bool whether this widget is associated with a data model.
     */
    protected function hasModel()
    {
        return $this->model instanceof Model && $this->attribute !== null;
    }

}
