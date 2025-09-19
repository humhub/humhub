<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\ui\form\widgets;

use humhub\modules\ui\icon\widgets\Icon;
use kartik\select2\Select2;
use Yii;
use yii\web\JsExpression;

/**
 * IconPicker form field widget
 *
 * Usage example:
 *
 * ```php
 *  <?= $activeForm->field($form, 'icon')->widget(IconPicker::class); ?>
 * ```
 *
 * @since 1.3
 */
class IconPicker extends Select2
{
    public $bsVersion = 5;

    /**
     * @var string optional icon provider id
     */
    public $lib;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->options['placeholder'] = Yii::t('UiModule.form', 'Select icon');
        $this->theme = Select2::THEME_BOOTSTRAP;
        $this->pluginOptions = [
            'escapeMarkup' => new JsExpression("function(m) { return m; }"),
        ];
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->value = (str_starts_with((string)$this->value, 'fa-'))
            ? substr($this->value, 3, strlen($this->value))
            : $this->value;

        return parent::run();
    }

    /**
     * @inheritdoc
     */
    public function renderInput()
    {
        $this->populateIconList();
        parent::renderInput();
    }

    /**
     * Populate data with icon list
     */
    protected function populateIconList()
    {
        foreach ($this->getIconNames() as $icon) {
            $title = $icon;
            if (str_starts_with((string) $title, 'fa-')) {
                $title = substr((string) $title, 3);
            }

            $this->data[$icon] = Icon::get(['name' => $icon, 'lib' => $this->lib]) . '&nbsp;&nbsp;' . $title;
        }
    }

    /**
     * Returns a list of available icons
     *
     * @return array a list of icons
     */
    public function getIconNames()
    {
        return Icon::getNames($this->lib);
    }

}
