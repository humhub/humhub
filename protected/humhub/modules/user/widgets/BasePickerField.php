<?php

namespace humhub\modules\user\widgets;

use Yii;
use yii\helpers\Html;
use \yii\helpers\Url;

/**
 * Abstract class for picker form fields.
 * 
 * Subclasses should at least overwrite the following fields:
 * 
 * - $defaultRoute for defining a default search query route
 * - $itemClass defines the type of item e.g. User/Space/...
 * - $itemKey defines the key attribute used as option values e.g. id/guid
 * 
 * And the following methods:
 * 
 * - getItemText for retrieving the option text for an item
 * - getItemImage for terieving the option image (if required)
 * 
 * 
 * The json result of a picker search query should return an array of items with the following key/values:
 * 
 *  - id: option value (itemKey) (required)
 *  - text: option text (required)
 *  - image: option image (optional)
 *  - priority: used to sort results (optional)
 *  - disabled: can be used to disable certain items (optional)
 *  - disabbleReason: text describing the reason why this item is disabled (optional)
 * 
 * @package humhub.modules_core.user.widgets
 * @since 1.2
 * @author buddha
 */
abstract class BasePickerField extends \yii\base\Widget
{

    /**
     * Defines the select input field id
     * 
     * @var string 
     */
    public $id;

    /**
     * Defines the javascript picker implementation.
     * 
     * @var string 
     */
    public $picker = 'ui.picker.Picker';

    /**
     * Default route used for search queries.
     * This can be overwritten by defining the $url.
     * 
     * @var string 
     */
    public $defaultRoute;

    /**
     * Search url used to overwrite the $defaultRoute for a picker isntance.
     *
     * @var string
     */
    public $url;

    /*
     * Used to overwrite select input field attributes. This array can be used for overwriting
     * texts, or other picker settings.
     * 
     * @var string
     */
    public $options = [];

    /**
     * Maximum amount of selection items.
     *
     * @var integer
     */
    public $maxSelection = 50;

    /**
     * Minimum character input before triggering search query.
     *
     * @var integer
     */
    public $minInput = 3;

    /**
     * Minimum character input before triggering search query.
     *
     * @var integer
     */
    public $maxInput = 20;

    /**
     * Array of item instances. If this array is set the picker will ignore the
     * actual model attribute and instead use this array as selection.
     * 
     * It this array is not set, the picker will try to load the items by means of the
     * model attribute 
     * 
     * @see BasePickerField::loadItems
     * @var array 
     */
    public $selection;

    /**
     * The item class used to load items by means of the model attribute value.
     * 
     * @var string 
     */
    public $itemClass;

    /**
     * The item key used as option value and loading items by attribute value.
     * e.g. id or guid
     * 
     * @var string 
     */
    public $itemKey;

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
     * Model attribute which holds the picker value. The referenced model attribute has to be an
     * array.
     * 
     * @var string 
     */
    public $attribute;

    /**
     * Used to retrieve the option text of a given $item.
     * 
     * @param \yii\db\ActiveRecord $item selected item
     * @return string item option text
     */
    protected abstract function getItemText($item);

    /**
     * Used to retrieve the option image url of a given $item.
     * 
     * @param \yii\db\ActiveRecord $item selected item
     * @return string image url or null if no selection image required.
     */
    protected abstract function getItemImage($item);

    /**
     * @inhertidoc
     */
    public function run()
    {
        // Prepare current selection and selection options
        $selection = [];
        $selectedOptions = $this->getSelectedOptions();
        foreach ($selectedOptions as $id => $option) {
            $selection[$id] = $option['data-text'];
        }

        $options = $this->getInputAttributes();
        $options['options'] = $selectedOptions;

        if ($this->form === null) {
            return Html::activeDropDownList($this->model, $this->attribute, $selection, $options);
        } else {
            return $this->form->field($this->model, $this->attribute)->dropDownList($selection, $options);
        }
    }

    /**
     * Prepares the selected options either by using the $selection array or by loading the items
     * by means of the model attribute value.
     * 
     * The resulting array has the following format:
     * 
     * [itemKey] => [
     *      'data-text' => itemText
     *      'data-image' => itemImage
     *      'selected' => selected
     * ]
     * 
     * Subclasses should overwrite the getItemText and getItemImage function for this purpose.
     * 
     * @return array 
     */
    protected function getSelectedOptions()
    {
        if (!$this->selection) {
            $attribute = $this->attribute;
            $this->selection = $this->loadItems($this->model->$attribute);
        }

        $result = [];
        foreach ($this->selection as $item) {
            $itemKey = $this->itemKey;
            $result[$item->$itemKey] = [
                'data-text' => $this->getItemText($item),
                'data-image' => $this->getItemImage($item),
                'selected' => 'selected'
            ];
        }
        return $result;
    }

    /**
     * Loads all items of the given $selection array.
     * The $selection array contains all selected itemKeys.
     * 
     * 
     * @param array $selection array of itemKeys
     * @return type array of items of type $itemClass or empty array for an empty selection
     */
    public function loadItems($selection = null)
    {
        if (empty($selection)) {
            return [];
        }

        $itemClass = $this->itemClass;
        return $itemClass::find()->where([$this->itemKey => $selection])->all();
    }

    /**
     * Assembles the select field input attributes by merging the default values with
     * the widgets $options array.
     * 
     * @return type
     */
    protected function getInputAttributes()
    {
        $attributes = array_merge($this->getAttributes(), $this->getTexts());
        return \yii\helpers\ArrayHelper::merge($attributes, $this->options);
    }

    /**
     * Returns an array of select field input attributes for this widget instance.
     * These attributes are used to configure the picker.
     * 
     * The following configuration attributes are available:
     * 
     *  - data-ui-picker: has to be set for all picker instances and can contain a javascript picker class.
     *  - data-picker-url: url used for search queries.
     *  - data-maximum-selection-length: maximum amount of allowed selection items.
     *  - data-maximum-input-length: maximum allowed characters for the search query.
     *  - data-minimum-input-length: minimum required characters for the search query.
     * 
     * @return array
     */
    protected function getAttributes()
    {
        return [
            'id' => $this->id,
            'data-ui-picker' => $this->picker,
            'data-picker-url' => $this->getUrl(),
            'multiple' => 'multiple',
            'size' => '1',
            'class' => 'form-control',
            'style' => 'width:100%',
            'data-maximum-selection-length' => $this->maxSelection,
            'data-maximum-input-length' => $this->maxInput,
            'data-minimum-input-length' => $this->minInput,
        ];
    }

    /**
     * Returns an array of texts configurations for this picker isntance.
     * Following texts can be configured:
     * 
     *  - data-placeholder: Placeholder text if no value is set.
     *  - data-placeholder-more: Placeholder text displayed if at least one item is set.
     *  - data-maximum-selected: Info message displayed if $maxSelection is exceed.
     *  - data-no-result: Empty result message.
     *  - data-format-ajax-error: Ajax error message.
     *  - data-load-more: Load more items text.
     *  - data-input-too-short: Info message displayed if $minInput characters is not exceed.
     *  - data-input-too-long: Info message displayed if $maxInput characters is exceed.
     * 
     * @return array
     */
    protected function getTexts()
    {
        $allowMultiple = $this->maxSelection !== 1;
        $result = [
            'data-placeholder' => Yii::t('UserModule.widgets_BasePickerField', 'Select {n,plural,=1{item} other{items}}', ['n' => ($allowMultiple) ? 2 : 1]),
            'data-placeholder-more' => Yii::t('UserModule.widgets_BasePickerField', 'Add more...'),
            'data-no-result' => Yii::t('UserModule.widgets_BasePickerField', 'Your search returned no matches.'),
            'data-format-ajax-error' => Yii::t('UserModule.widgets_BasePickerField', 'An unexpected error occured while loading the result.'),
            'data-load-more' => Yii::t('UserModule.widgets_BasePickerField', 'Load more'),
            'data-input-too-short' => Yii::t('UserModule.widgets_BasePickerField', 'Please enter at least {n} character', ['n' => $this->minInput]),
            'data-input-too-long' => Yii::t('UserModule.widgets_BasePickerField', 'You reached the maximum number of allowed charachters ({n}).', ['n' => $this->maxInput])
        ];

        if ($this->maxSelection) {
            $result['data-maximum-selected'] = Yii::t('UserModule.widgets_BasePickerField', 'This field only allows a maximum of {n,plural,=1{# item} other{# items}}.', ['n' => $this->maxSelection]);
        }
        return $result;
    }

    /**
     * Returns the url for this picker instance. If no $url is set we use the $defaultRoute for creating the url.
     * 
     * @return strings
     */
    protected function getUrl()
    {
        return ($this->url) ? $this->url : Url::to([$this->defaultRoute]);
    }

}
