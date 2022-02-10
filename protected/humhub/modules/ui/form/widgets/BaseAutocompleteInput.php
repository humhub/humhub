<?php

namespace humhub\modules\ui\form\widgets;

use humhub\components\ActiveRecord;
use Yii;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Abstract class for form fields with autocomplete.
 *
 */
abstract class BaseAutocompleteInput extends JsInputWidget
{
    /**
     * Defines the javascript picker implementation.
     *
     * @var string
     */
    public $jsWidget = 'ui.autocomplete.Autocomplete';

    /**
     * Default route used for search queries.
     *
     * @var string
     */
    public $url;

    /**
     * If the ActiveForm is set, it will be used to create the picker field,
     * otherwise it's created by Html::activeDropDownList
     *
     * @var \yii\widgets\ActiveForm
     */
    public $form;

    /**
     * Model instance.
     *
     * @var \yii\db\ActiveRecord
     */
    public $model;

    /**
     * Model attribute which holds the value. The referenced model attribute has to be an
     * array.
     *
     * @var string
     */
    public $attribute;

    /**
     * Minimum number of characters to load suggestions
     * @var int
     */
    public $minInput = 3;

    /**
     * @inhertidoc
     */
    public function beforeRun()
    {
        return parent::beforeRun();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('@ui/form/widgets/views/autocompleteInput', [
            'form' => $this->form,
            'model' => $this->model,
            'field' => $this->attribute,
            'options' => $this->getOptions(),
            'inputId' => $this->getId(true)
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function getOptions()
    {
        $widgetOptions = [
            'id' => $this->getId(true),
            'data-ui-widget' => $this->jsWidget,
            'data-ui-init' => '',
            'data-url' => $this->url,
            'data-min-input' => $this->minInput
        ];

        return array_merge($this->options, $widgetOptions);
    }
}
