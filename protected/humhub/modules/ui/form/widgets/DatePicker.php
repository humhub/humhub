<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\form\widgets;

use humhub\assets\DatePickerRussianLanguageAsset;
use humhub\helpers\Html;
use Yii;
use yii\helpers\FormatConverter;
use yii\helpers\Json;
use yii\jui\DatePicker as BaseDatePicker;
use yii\jui\DatePickerLanguageAsset;
use yii\jui\JuiAsset;

/**
 * DatePicker form field widget
 *
 * @since 1.3.0
 * @inheritdoc
 * @package humhub\modules\ui\form\widgets
 */
class DatePicker extends BaseDatePicker
{
    public const LANGUAGEMAPPING = [
        'nb-NO' => 'nb',
        'nn-NO' => 'nn',
        'fa-IR' => 'fa',
        'an' => null,
        'uz' => null,
        'ht' => null,
        'am' => null,
    ];

    public $pickerLanguage;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->dateFormat === null) {
            $this->dateFormat = Yii::$app->formatter->dateInputFormat;
        }

        Html::addCssClass($this->options, 'form-control');

        parent::init();

        if ($this->attribute && $this->hasModel()) {
            $attributeName = Html::getAttributeName($this->attribute);
            if ($attributeName && $this->model->hasErrors($attributeName)) {
                Html::addCssClass($this->options, 'is-invalid');
            }
        }

        $this->pickerLanguage = $this->language ?: Yii::$app->language;
        $this->pickerLanguage = (array_key_exists($this->pickerLanguage, static::LANGUAGEMAPPING))
            ? static::LANGUAGEMAPPING[$this->pickerLanguage]
            : $this->pickerLanguage;

        if (!$this->pickerLanguage) {
            $this->pickerLanguage = 'en-US';
        }

        $this->pickerLanguage = str_replace('_', '-', $this->pickerLanguage);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        /**
         * HUMHUB PATCH: Language Mapping + Prevent loading language files for all english based languages, since DatePickerLanguageAsset tries
         * to load a fallback language e.g. for `en-GB` -> 'en'.
         */

        $this->options['autocomplete'] = 'off';

        $language = $this->language ?: Yii::$app->language;

        echo $this->renderWidget() . "\n";

        $containerID = $this->inline ? $this->containerOptions['id'] : $this->options['id'];

        if (str_starts_with($this->dateFormat, 'php:')) {
            $this->clientOptions['dateFormat'] = FormatConverter::convertDatePhpToJui(substr($this->dateFormat, 4));
        } else {
            $this->clientOptions['dateFormat'] = FormatConverter::convertDateIcuToJui($this->dateFormat, 'date', $language);
        }

        if ($this->pickerLanguage !== 'en-US' && $this->pickerLanguage !== 'en') {
            $this->registerLanguageAsset();

            $options = Json::htmlEncode($this->clientOptions);
            $this->pickerLanguage = Html::encode($this->pickerLanguage);
            $this->getView()->registerJs("jQuery('#{$containerID}').datepicker($.extend({}, $.datepicker.regional['{$this->pickerLanguage}'], $options));");
        } else {
            $this->registerClientOptions('datepicker', $containerID);
        }

        $this->registerClientEvents('datepicker', $containerID);
        JuiAsset::register($this->getView());
    }

    private function registerLanguageAsset()
    {

        if ($this->pickerLanguage === 'ru') {
            DatePickerRussianLanguageAsset::register($this->getView());
            return;
        }

        $assetBundle = DatePickerLanguageAsset::register($this->getView());
        if (str_starts_with((string) $this->pickerLanguage, 'en')) {
            $assetBundle->autoGenerate = false;
            $assetBundle->js[] = "ui/i18n/datepicker-{$this->pickerLanguage}.js";
        } else {
            $assetBundle->language = $this->pickerLanguage;
        }
    }
}
