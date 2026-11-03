<?php

namespace humhub\widgets\form;

use humhub\helpers\Html;
use Stringable;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

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
 * The JSON answer of a legacy search URL (`$url`/`$defaultRoute`, `?keyword=<term>`) is a list of
 * `{id, text, subText?, image?, disabled?}`; `image` a URL, `#colour`, Tabler icon name or an image
 * description (see docs/develop/ui-js-vuejs-picker.md), never HTML. The widget renders a native
 * `<select>` the Vue `PickerInput` mounts on (`ui.picker`).
 *
 * @package humhub.modules_core.user.widgets
 * @since 1.2
 * @author buddha
 */
abstract class BasePicker extends JsInputWidget
{
    /**
     * Legacy keys of getData() and the `<select>` attributes they become.
     */
    private const DATA_ATTRIBUTES = [
        'add-options' => 'data-picker-allow-new',
        'item-key' => 'data-picker-item-key',
        'maximum-selection-length' => 'data-picker-max-selection',
        'maximum-input-length' => 'data-picker-max-input',
        'minimum-input-length' => 'data-picker-min-input',
        'placeholder' => 'data-picker-placeholder',
        'placeholder-more' => 'data-picker-placeholder-more',
    ];

    /**
     * Legacy message keys of getData() and their `messages` keys.
     */
    private const DATA_MESSAGES = [
        'no-result' => 'noResults',
        'format-ajax-error' => 'error',
        'input-too-short' => 'inputTooShort',
        'locked-text' => 'locked',
        'maximum-selected' => 'maximumSelected',
    ];

    /**
     * Legacy keys of getData() handled separately or no longer used.
     */
    private const DATA_HANDLED = [
        'picker-url', 'default-results', 'disabled-items', 'locked-items', 'disabled',
        'picker-focus', 'load-more', 'input-too-long',
    ];

    /**
     * Not used since 1.20: the `<select>` is enhanced by the `ui.picker` UI addition.
     *
     * @var string|null
     */
    public $jsWidget = null;

    /**
     * Disabled items
     * @var array
     */
    public $disabledItems;

    /**
     * Item keys (values of $itemKey, e.g. guids) which are currently selected but which the
     * current user is not allowed to remove from the selection. The picker will still display
     * these as selected, but hides their remove ("x") control and refuses client side removal.
     * Locked items are always rendered first, followed by the removable ones and then, once the
     * user starts adding more, any newly picked items - a predictable fixed > editable > add
     * layout, without callers having to sort their selection themselves.
     *
     * Note: this is a UX convenience only - it does not replace server side authorization, the
     * form/controller handling the submitted value still has to enforce this independently.
     *
     * @since 1.18.6
     * @var array
     */
    public $lockedItems;

    /**
     * Disables the picker field
     * @var bool
     */
    public $disabled = false;

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
     * @var int
     */
    public $maxSelection = 50;

    /**
     * Minimum characters input before triggering search query.
     *
     * @var int
     */
    public $minInput = 3;

    /**
     * Maximum characters input before triggering search query.
     *
     * @var int
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
     * Available values: `id` or `guid`
     *
     * @since v1.3 'id' by default
     *
     * @var string
     */
    public $itemKey = 'id';

    /**
     * If the ActiveForm is set, it will be used to create the picker field,
     * otherwise it's created by Html::activeDropDownList
     *
     * @var \humhub\widgets\form\ActiveForm
     */
    public $form;

    /**
     * Model instance.
     *
     * @var ActiveRecord
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
     * If set to true, the picker will allow adding new options not included in the current search result.
     *
     * > Note: New values will be submitted as '_add:term' to distinguish between new and existing options. The filtering and insertion
     * has to be handled manually within the submit/form logic.
     *
     * e.g.:
     *
     * ```php
     * foreach($values as $value) {
     *     if(strpos($value, '_add:') === 0) {
     *         $newValue = substr($value, strlen('_add:'));
     *         // Save new item with newValue
     *     } else {
     *         $item = MyItem::findOne((int) $value);
     *         // ...
     *     }
     * }
     * ```
     *
     * @var bool|null
     */
    public $addOptions = false;


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
     * @var bool
     */
    public $focus = false;

    /**
     * Shows the chosen items as rows below the field instead of chips in it.
     *
     * @var bool
     * @since 1.20
     */
    public bool $listed = false;

    /**
     * Items a named source suggests on focus, before anything is typed (0: none). Ignored
     * when $defaultResults are given.
     *
     * @var int
     * @since 1.20
     */
    public int $suggested = 0;

    /**
     * Items a search lists at most; null: 8 for a remote source, all for static options.
     *
     * @var int|null
     * @since 1.20
     */
    public ?int $maxResults = null;

    /**
     * @inheritdoc
     * @var bool
     */
    public $init = false;

    /**
     * Used to retrieve the option text of a given $item.
     *
     * @param ActiveRecord $item selected item
     * @return string item option text
     */
    abstract protected function getItemText($item);

    /**
     * Used to retrieve the option image of a given $item.
     *
     * @param ActiveRecord $item selected item
     * @return string|array|Stringable|null an image URL, `#colour`, Tabler icon name, or an image description
     * array (`['type' => 'user'|'space'|'icon'|'color'|'url', …]`); never HTML
     */
    abstract protected function getItemImage($item);

    /**
     * @inhertidoc
     */
    public function run()
    {
        if ($this->selection !== null && !is_array($this->selection)) {
            $this->selection = [$this->selection];
        }

        // Prepare current selection and selection options
        $selection = [];
        $selectedOptions = $this->getSelectedOptions();
        foreach ($selectedOptions as $id => $option) {
            $selection[$id] = $option['data-text'];
        }

        $options = $this->getPickerOptions();
        $options['options'] = $selectedOptions;

        if ($this->form != null) {
            return $this->form->field($this->model, $this->attribute)->dropDownList($selection, $options);
        } elseif ($this->model != null) {
            return Html::activeDropDownList($this->model, $this->attribute, $selection, $options);
        } else {
            $name = (!$this->name) ? 'pickerField' : $this->name;
            return Html::dropDownList($name, $this->value, $selection, $options);
        }
    }

    /**
     * The attributes of the `<select>`: getAttributes(), the picker data, then $options. The
     * JsWidget defaults ($events, $fadeIn, $visible) apply as in getOptions().
     *
     * @since 1.20
     */
    protected function getPickerOptions(): array
    {
        $this->setDefaultOptions();

        $result = ArrayHelper::merge(
            $this->getAttributes(),
            $this->getPickerData($this->getData()),
            $this->getLegacyOptions(),
            ['id' => $this->getId()],
        );

        if (!$this->visible) {
            Html::addCssStyle($result, 'display:none');
        }

        return $result;
    }

    /**
     * The `$options`, Select2 attributes of a caller (`data-placeholder`, …) translated into their
     * `data-picker-*` attributes; `data-tags` (no longer used) dropped.
     *
     * @since 1.20
     */
    private function getLegacyOptions(): array
    {
        $options = $this->options;
        foreach (self::DATA_ATTRIBUTES as $key => $attribute) {
            if (array_key_exists('data-' . $key, $options)) {
                $options[$attribute] ??= $options['data-' . $key];
                unset($options['data-' . $key]);
            }
        }
        unset($options['data-tags']);

        return $options;
    }

    /**
     * Translates the (legacy-keyed) result of getData() into the `data-picker-*` attributes and
     * `data-picker-config` of the markup contract (docs/develop/ui-js-vuejs-picker.md). A key it
     * does not know passes through as `data-<key>`.
     *
     * @since 1.20
     */
    final protected function getPickerData(array $data): array
    {
        $result = ['data-ui-picker' => true];
        $config = ['messages' => []];

        $source = $this->getSource();
        if ($source !== null) {
            $result['data-picker-source'] = $source['source'];
            if (!empty($source['params'])) {
                $config['sourceParams'] = $source['params'];
            }
        } elseif (!empty($data['picker-url'])) {
            $result['data-picker-url'] = $data['picker-url'];
        }

        foreach ($data as $key => $value) {
            if (isset(self::DATA_ATTRIBUTES[$key])) {
                if ($value !== null && $value !== false && $value !== '') {
                    $result[self::DATA_ATTRIBUTES[$key]] = $value === true ? 'true' : $value;
                }
            } elseif (isset(self::DATA_MESSAGES[$key])) {
                if ($value !== null && $value !== '') {
                    $config['messages'][self::DATA_MESSAGES[$key]] = $value;
                }
            } elseif (!in_array($key, self::DATA_HANDLED, true)) {
                $result['data-' . $key] = $value;
            }
        }

        if (!empty($data['default-results'])) {
            $config['defaultResults'] = array_values(array_map(
                static function ($option) {
                    // buildItemOption() keys, or the JSON shape of a legacy search answer
                    $image = $option['data-image'] ?? $option['image'] ?? null;
                    return [
                        'id' => (string)($option['data-id'] ?? $option['id']),
                        'text' => $option['data-text'] ?? $option['text'] ?? '',
                        'subText' => $option['data-sub-text'] ?? $option['subText'] ?? null,
                        'image' => $image instanceof Stringable ? (string)$image : $image,
                    ];
                },
                $data['default-results'],
            ));
        }
        if (!empty($data['disabled-items'])) {
            $config['disabledIds'] = array_map('strval', (array)$data['disabled-items']);
        }
        if (!empty($data['locked-items'])) {
            $config['lockedIds'] = array_map('strval', array_values((array)$data['locked-items']));
        }

        if (isset($result['data-picker-allow-new'])) {
            // New items are posted as `_add:<term>`, see $addOptions
            $result['data-picker-new-prefix'] = '_add:';
        }

        if ($this->listed) {
            $result['data-picker-listed'] = 'true';
        }
        if ($this->suggested > 0) {
            $result['data-picker-suggested'] = $this->suggested;
        }
        if ($this->maxResults !== null) {
            $result['data-picker-max-results'] = $this->maxResults;
        }

        $config = array_filter($config, static fn($value) => $value !== []);
        if ($config !== []) {
            $result['data-picker-config'] = $config;
        }

        return $result;
    }

    /**
     * The named source of the picker (`['source' => '<name>', 'params' => [...]]`, see
     * `registerPickerSource()` in docs/develop/ui-js-vuejs-picker.md), or null for the legacy
     * search URL (getUrl()) — or static options when getData() drops `picker-url`.
     *
     * @since 1.20
     */
    public function getSource(): ?array
    {
        return null;
    }

    /**
     * Used to retrieve the option sub text (the second line in the result list) of a given $item.
     *
     * @param mixed $item
     * @return string|null
     * @since 1.20
     */
    protected function getItemSubText($item): ?string
    {
        return null;
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
        if ($this->selection === null && $this->model !== null) {
            $attribute = $this->attribute;
            if (strrpos($attribute, '[') !== false) {
                $this->selection = $this->loadItems(Html::getAttributeValue($this->model, $attribute));
            } else {
                $this->selection = $this->loadItems($this->model->$attribute);
            }
        }

        if (!$this->selection) {
            $this->selection = [];
        }

        // Note: sorting works on a local copy, $this->selection is a public property owned
        // by the caller and must not be reordered or re-keyed behind its back.
        $selection = array_filter($this->selection);

        if (!empty($this->lockedItems)) {
            $selection = $this->sortLockedItemsFirst($selection);
        }

        $result = [];
        foreach ($selection as $item) {
            $result[$this->getItemKey($item)] = $this->buildItemOption($item);
        }
        return $result;
    }

    /**
     * Sorts the given (already selected) $items so that locked items (see $lockedItems) come
     * first, followed by the removable ones - a predictable "fixed items > editable items >
     * add new item" layout for every picker that uses $lockedItems, rather than each usage
     * site having to sort its selection itself. The initial render order of a multi select
     * picker is fully determined by the order of the underlying <option> elements, so sorting
     * here is sufficient - no client side reordering is needed.
     *
     * usort() is stable since PHP 8, so the relative order within the locked/removable groups
     * themselves is preserved. Newly added items (picked via search) are unaffected, they are
     * simply appended after the initial selection by the picker widget on the client.
     *
     * @param array $items
     * @return array
     */
    protected function sortLockedItemsFirst(array $items): array
    {
        usort($items, function ($a, $b) {
            return (int)!$this->isLockedItem($a) <=> (int)!$this->isLockedItem($b);
        });

        return $items;
    }

    /**
     * Checks whether the given item is locked, meaning the current user is not allowed to
     * remove it from the selection again (see $lockedItems).
     *
     * Item keys are compared as strings: they end up as <option value> attributes on the
     * client and are therefore strings there in any case, while $lockedItems is typically
     * built from model attributes and may well contain integers (e.g. the default
     * $itemKey = 'id').
     *
     * @param mixed $item
     * @return bool
     */
    protected function isLockedItem($item): bool
    {
        return in_array((string)$this->getItemKey($item), $this->getLockedItemKeys(), true);
    }

    /**
     * @return string[] $lockedItems normalized to strings, see isLockedItem()
     */
    protected function getLockedItemKeys(): array
    {
        return array_map('strval', (array)$this->lockedItems);
    }

    /**
     * Responsible for building the option data for an item.
     *
     * @param mixed $item
     * @param bool $selected
     * @return array
     */
    protected function buildItemOption($item, $selected = true)
    {
        $image = $this->getItemImage($item);
        if ($image instanceof Stringable) {
            $image = (string)$image;
        }

        $result = [
            'data-id' => $this->getItemKey($item),
            'data-text' => $this->getItemText($item),
        ];

        if ($image !== null && $image !== '') {
            $result['data-image'] = $image;
        }

        $subText = $this->getItemSubText($item);
        if ($subText !== null && $subText !== '') {
            $result['data-sub-text'] = $subText;
        }

        if ($this->isLockedItem($item)) {
            $result['data-locked'] = true;
        }

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
     * @param mixed $item
     * @return string
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
     * @param array|string $selection array of itemKeys or comma separated string
     * @return array of items of type $itemClass or empty array for an empty selection
     */
    public function loadItems($selection = null)
    {
        if (empty($selection)) {
            return [];
        }

        if ($selection instanceof ActiveQuery) {
            return $selection->all();
        }

        // For older version (prior 1.2) - try to convert comma separated list to array
        if (!is_array($selection)) {
            $selection = explode(',', $selection);
        }

        $itemClass = $this->itemClass;
        return $itemClass::find()->where([$this->itemKey => $selection])->all();
    }

    /**
     * @inheritdoc
     */
    protected function getAttributes()
    {
        $attributes = [
            'multiple' => 'multiple',
            'size' => '1',
            'class' => 'form-control',
        ];

        if ($this->disabled) {
            $attributes['disabled'] = true;
        }

        if ($this->focus) {
            $attributes['autofocus'] = true;
        }

        return $attributes;
    }

    /**
     * Returns the settings of this picker instance under their legacy keys, which subclasses
     * override (e.g. `placeholder`, `placeholder-more`, `no-result`, `maximum-selected`,
     * `picker-url`, `item-key`, `add-options`, `minimum-input-length`, `default-results`,
     * `disabled-items`, `locked-items`, `locked-text`). getPickerData() translates them into the
     * `data-picker-*` attributes and `data-picker-config` of the `<select>`; a key it does not
     * know is rendered as `data-<key>`.
     *
     * @return array
     */
    protected function getData()
    {
        $allowMultiple = $this->maxSelection !== 1;

        $placeholder = ($this->placeholder != null) ? $this->placeholder : Yii::t('UserModule.chooser', 'Select {n,plural,=1{item} other{items}}', ['n' => ($allowMultiple) ? 2 : 1]);
        $placeholderMore = ($this->placeholderMore != null) ? $this->placeholderMore : Yii::t('UserModule.chooser', 'Select...');

        $result = [
            'add-options' => $this->addOptions,
            'item-key' => $this->itemKey,
            'picker-url' => $this->getUrl(),
            'picker-focus' => $this->focus,
            'maximum-selection-length' => $this->maxSelection,
            'maximum-input-length' => $this->maxInput,
            'minimum-input-length' => $this->minInput,
            'placeholder' => $placeholder,
            'placeholder-more' => $placeholderMore,
            'no-result' => Yii::t('UserModule.chooser', 'Your search returned no matches.'),
            'format-ajax-error' => Yii::t('UserModule.chooser', 'An unexpected error occurred while loading the result.'),
            'load-more' => Yii::t('UserModule.chooser', 'Load more'),
            'input-too-short' => Yii::t('UserModule.chooser', 'Please enter at least {n} character', ['n' => $this->minInput]),
            'input-too-long' => Yii::t('UserModule.chooser', 'You reached the maximum number of allowed characters ({n}).', ['n' => $this->maxInput]),
            'default-results' => $this->getDefaultResultData(),
            'disabled' => $this->disabled,
        ];

        if (!empty($this->disabledItems)) {
            $result['disabled-items'] = $this->disabledItems;
        }

        if (!empty($this->lockedItems)) {
            $result['locked-items'] = $this->getLockedItemKeys();
            $result['locked-text'] = Yii::t('UserModule.chooser', 'You are not allowed to remove this item.');
        }

        if ($this->maxSelection) {
            $result['maximum-selected'] = Yii::t('UserModule.chooser', 'This field only allows a maximum of {n,plural,=1{# item} other{# items}}.', ['n' => $this->maxSelection]);
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
     * @return string
     */
    protected function getUrl()
    {
        return $this->url ?: Url::to([$this->defaultRoute]);
    }
}
