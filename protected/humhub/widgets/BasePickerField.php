<?php

namespace humhub\widgets;

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
 * - getItemImage for retrieving the option image (if required)
 * 
 * 
 * The json result of a picker search query should return an array of items with the following key/values:
 * 
 *  - id: option value (itemKey) (required)
 *  - text: option text (required)
 *  - image: option image (optional)
 *  - priority: used to sort results (optional)
 *  - disabled: can be used to disable certain items (optional)
 *  - disabbledText: text describing the reason why the item is disabled (optional)
 * 
 * @package humhub.modules_core.user.widgets
 * @since 1.2
 * @author buddha
 */
abstract class BasePickerField extends InputWidget
{

    /**
     * Defines the javascript picker implementation.
     * 
     * @var string 
     */
    public $jsWidget = 'ui.picker.Picker';

    /**
     * Disabled items
     */
    public $disabledItems;

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
     * @var array Array of item instances used as long the minInput is not exceed. 
     */
    public $defaultResults = [];

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
     * @deprecated since 1.2.2 use $name instead
     */
    public $formName;

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
     * Can be used to overwrite the default placeholder.
     * @var string
     */
    public $placeholder;

    /**
     * Can be used to overwrite the default add more placeholder.
     * @var string 
     */
    public $placeholderMore;

    /**
     * If set to true the picker will be focused automatically.
     * 
     * @var boolean
     */
    public $focus = false;

    /**
     * @inheritdoc
     * @var boolean 
     */
    public $init = true;

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
        \humhub\assets\Select2BootstrapAsset::register($this->view);

        //Only for compatibility
        if(empty($this->name)) {
            $this->name = $this->formName;
        }

        if ($this->selection != null && !is_array($this->selection)) {
            $this->selection = [$this->selection];
        }

        // Prepare current selection and selection options
        $selection = [];
        $selectedOptions = $this->getSelectedOptions();
        foreach ($selectedOptions as $id => $option) {
            $selection[$id] = $option['data-text'];
        }

        $options = $this->getOptions();
        $options['options'] = $selectedOptions;

        if ($this->form != null) {
            return $this->form->field($this->model, $this->attribute)->dropDownList($selection, $options);
        } else if ($this->model != null) {
            return Html::activeDropDownList($this->model, $this->attribute, $selection, $options);
        } else {
            $name = (!$this->name) ? 'pickerField' : $this->name;
            return Html::dropDownList($name, $this->value, $selection, $options);
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
        if (!$this->selection && $this->model != null) {
            $attribute = $this->attribute;

            $this->selection = $this->loadItems($this->model->$attribute);
        }

        if (!$this->selection) {
            $this->selection = [];
        }

        $result = [];
        foreach ($this->selection as $item) {
            if (!$item) {
                continue;
            }

            $result[$this->getItemKey($item)] = $this->buildItemOption($item);
        }
        return $result;
    }

    /**
     * Responsible for building the option data for an item.
     * 
     * @param type $item
     * @param type $selected
     * @return string
     */
    protected function buildItemOption($item, $selected = true)
    {
        $result = [
            'data-id' => $this->getItemKey($item),
            'data-text' => $this->getItemText($item),
            'data-image' => $this->getItemImage($item),
        ];

        if ($selected) {
            $result['selected'] = 'selected';
        }

        return $result;
    }

    /**
     * Returns the item key which is used as option value. By default we use 
     * the $itemKey attribibute of $item.
     * 
     * e.g. $itemKey = 'id'
     * 
     * @param type $item
     * @return type
     */
    protected function getItemKey($item)
    {
        $itemKey = $this->itemKey;
        return $item->$itemKey;
    }

    /**
     * Loads all items of the given $selection array.
     * The $selection array contains all selected itemKeys.
     * 
     * @param array $selection array of itemKeys
     * @return type array of items of type $itemClass or empty array for an empty selection
     */
    public function loadItems($selection = null)
    {
        if (empty($selection)) {
            return [];
        }

        // For older version (prior 1.2) - try to convert comma separated list to array 
        if (!is_array($selection)) {
            $selection = explode(',', $selection);
        }

        $itemClass = $this->itemClass;
        return $itemClass::find()->where([$this->itemKey => $selection])->all();
    }

    /*
     * @inheritdoc
     */
    protected function getAttributes()
    {
        return [
            'multiple' => 'multiple',
            'size' => '1',
            'class' => 'form-control',
            'style' => 'width:100%',
            'title' => $this->placeholder
        ];
    }

    /**
     * Returns an array of data attributes for this picker isntance.
     * Following data attributes can be configured by default:
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
    protected function getData()
    {
        $allowMultiple = $this->maxSelection !== 1;

        $placeholder = ($this->placeholder != null) ? $this->placeholder : Yii::t('UserModule.widgets_BasePickerField', 'Select {n,plural,=1{item} other{items}}', ['n' => ($allowMultiple) ? 2 : 1]);
        $placeholderMore = ($this->placeholderMore != null) ? $this->placeholderMore : Yii::t('UserModule.widgets_BasePickerField', 'Add more...');

        $result = [
            'picker-url' => $this->getUrl(),
            'picker-focus' => ($this->focus) ? 'true' : null,
            'disabled-items' => (!$this->disabledItems) ? null : $this->disabledItems,
            'maximum-selection-length' => $this->maxSelection,
            'maximum-input-length' => $this->maxInput,
            'minimum-input-length' => $this->minInput,
            'placeholder' => $placeholder,
            'placeholder-more' => $placeholderMore,
            'no-result' => Yii::t('UserModule.widgets_BasePickerField', 'Your search returned no matches.'),
            'format-ajax-error' => Yii::t('UserModule.widgets_BasePickerField', 'An unexpected error occurred while loading the result.'),
            'load-more' => Yii::t('UserModule.widgets_BasePickerField', 'Load more'),
            'input-too-short' => Yii::t('UserModule.widgets_BasePickerField', 'Please enter at least {n} character', ['n' => $this->minInput]),
            'input-too-long' => Yii::t('UserModule.widgets_BasePickerField', 'You reached the maximum number of allowed charachters ({n}).', ['n' => $this->maxInput]),
            'default-results' => $this->getDefaultResultData()
        ];

        if ($this->maxSelection) {
            $result['maximum-selected'] = Yii::t('UserModule.widgets_BasePickerField', 'This field only allows a maximum of {n,plural,=1{# item} other{# items}}.', ['n' => $this->maxSelection]);
        }
        return $result;
    }

    protected function getDefaultResultData()
    {
        $result = [];
        foreach ($this->defaultResults as $item) {
            $result[] = $this->buildItemOption($item);
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
