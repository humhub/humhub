<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets\form;

use humhub\components\icon\TablerIconProvider;
use humhub\helpers\Html;
use humhub\widgets\Icon;
use Yii;

/**
 * A picker of an icon name (Tabler by default), each suggestion showing its icon.
 *
 * Usage example:
 *
 * ```php
 *  <?= $activeForm->field($form, 'icon')->widget(IconPicker::class); ?>
 * ```
 *
 * Since 1.20 a single `BasePicker` (the Vue `PickerInput`) on the named source `icon`; before, a
 * kartik Select2. The `<select>` holds the current value only (kept even when it is not a name of
 * the library); the names reach the page once per library through the `ui.picker` JS config
 * (`iconNames`). A stored Font Awesome 4 name is preselected as the Tabler icon it renders as.
 *
 * @since 1.3
 */
class IconPicker extends BasePicker
{
    /**
     * @var string|null the icon library, null for Tabler
     */
    public $lib;

    /**
     * @inheritdoc
     */
    public $minInput = 0;

    /**
     * @inheritdoc
     */
    public $maxSelection = 1;

    /**
     * @inheritdoc
     * `null` lists all icons of the library.
     */
    public ?int $maxResults = null;

    public function init()
    {
        $this->placeholder ??= Yii::t('base', 'Select icon');
        parent::init();
    }

    public function run()
    {
        $value = $this->normalize($this->hasModel() ? Html::getAttributeValue($this->model, $this->attribute) : $this->value);

        $this->registerIconNames();
        $this->maxResults ??= count($this->getIconNames());

        $options = $this->getPickerOptions();
        $options['prompt'] = '';
        $options['options'] = [];
        $items = [];
        if ($value !== null) {
            $items[$value] = $value;
            $options['options'][$value] = array_filter([
                'data-text' => $value,
                'data-image' => $this->getItemImage($value),
            ]);
        }

        $name = $this->hasModel() ? Html::getInputName($this->model, $this->attribute) : ($this->name ?: 'icon');

        return Html::dropDownList($name, $value, $items, $options);
    }

    /**
     * @inheritdoc
     * @since 1.20
     */
    public function getSource(): ?array
    {
        return ['source' => 'icon', 'params' => ['lib' => $this->getLib()]];
    }

    public function getIconNames()
    {
        return Icon::getNames($this->lib);
    }

    /**
     * Hands the names of the library to the `icon` picker source: `ui.picker` config
     * `iconNames.<lib>`, comma-separated — a string, so that the merge of repeated
     * registerJsConfig() calls replaces it instead of appending the list again.
     */
    protected function registerIconNames(): void
    {
        $this->getView()->registerJsConfig('ui.picker', [
            'iconNames' => [$this->getLib() => implode(',', array_values($this->getIconNames()))],
        ]);
    }

    protected function getAttributes()
    {
        $attributes = parent::getAttributes();
        unset($attributes['multiple'], $attributes['size']);

        return $attributes;
    }

    protected function getData()
    {
        $data = parent::getData();
        unset($data['picker-url']);

        return $data;
    }

    protected function getUrl()
    {
        return null;
    }

    protected function getItemText($item)
    {
        return $item;
    }

    protected function getItemImage($item)
    {
        return $this->isTabler() ? 'ti-' . $item : null;
    }

    protected function getItemKey($item)
    {
        return $item;
    }

    private function normalize($value): ?string
    {
        $value = Icon::stripPrefix((string)$value) ?: null;

        return $value !== null && $this->isTabler() ? TablerIconProvider::resolveLegacyName($value) : $value;
    }

    private function getLib(): string
    {
        return $this->lib ?? TablerIconProvider::ID;
    }

    private function isTabler(): bool
    {
        return $this->getLib() === TablerIconProvider::ID;
    }
}
