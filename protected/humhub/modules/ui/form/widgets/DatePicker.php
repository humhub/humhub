<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\form\widgets;

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

            /**
             * HUMHUB PATCH: Some bundled jQuery UI locale files use month abbreviations that don't
             * match the ones PHP's intl/ICU data returns for the very same locale (e.g. jQuery UI's
             * `en-GB` file says "Sep" for September, while ICU/CLDR says "Sept" for en-GB). Since the
             * server uses ICU (via DbDateValidator / Yii Formatter::asDate()) to validate and
             * (re-)format the very same value, such a mismatch makes a date picked in the UI fail
             * server-side validation on save, and later makes the picker unable to re-parse the
             * server-formatted value (falling back to today's date). Overriding monthNames /
             * monthNamesShort with names generated from that same ICU locale keeps client and server
             * in sync, whatever locale is used.
             */
            $this->clientOptions += $this->getIntlMonthNames($language);

            $options = Json::htmlEncode($this->clientOptions);
            $this->pickerLanguage = Html::encode($this->pickerLanguage);
            $this->getView()->registerJs("jQuery('#{$containerID}').datepicker($.extend({}, $.datepicker.regional['{$this->pickerLanguage}'], $options));");
        } else {
            $this->registerClientOptions('datepicker', $containerID);
        }

        $this->registerClientEvents('datepicker', $containerID);

        // Hide date picker on press Enter on phone browser
        $this->getView()->registerJs('jQuery("#' . $containerID . '").on("blur", function() {
            jQuery(document).on("mousedown", ".ui-datepicker", function() {
              jQuery(this).data("isClicked", true);
            }).on("mouseup", function() {
                setTimeout(() => jQuery(".ui-datepicker").data("isClicked", false), 0);
            });
            if (jQuery(".ui-datepicker").data("isClicked") === false) {
                jQuery(this).datepicker("hide");
            }
        });');

        JuiAsset::register($this->getView());
    }

    /**
     * Generates `monthNames` / `monthNamesShort` arrays for the given locale using PHP's intl/ICU
     * data, i.e. the very same data source used server-side to validate and format dates (see
     * DbDateValidator and \Yii::$app->formatter->asDate()). This guarantees the jQuery UI datepicker
     * always displays/accepts the same month abbreviations the server expects, even where a bundled
     * jQuery UI locale file disagrees with ICU (e.g. "Sep" vs "Sept" for September in `en-GB`).
     *
     * @param string $locale
     * @return array{monthNames?: string[], monthNamesShort?: string[]}
     */
    private function getIntlMonthNames(string $locale): array
    {
        if (!class_exists(\IntlDateFormatter::class)) {
            return [];
        }

        try {
            $shortFormatter = new \IntlDateFormatter($locale, \IntlDateFormatter::NONE, \IntlDateFormatter::NONE);
            $shortFormatter->setPattern('MMM');

            $longFormatter = new \IntlDateFormatter($locale, \IntlDateFormatter::NONE, \IntlDateFormatter::NONE);
            $longFormatter->setPattern('MMMM');

            $monthNames = [];
            $monthNamesShort = [];
            for ($month = 1; $month <= 12; $month++) {
                $timestamp = mktime(0, 0, 0, $month, 1, 2000);
                $monthNames[] = $longFormatter->format($timestamp);
                $monthNamesShort[] = $shortFormatter->format($timestamp);
            }
        } catch (\Throwable $e) {
            return [];
        }

        return [
            'monthNames' => $monthNames,
            'monthNamesShort' => $monthNamesShort,
        ];
    }

    private function registerLanguageAsset()
    {
        $assetBundle = DatePickerLanguageAsset::register($this->getView());
        if (str_starts_with((string) $this->pickerLanguage, 'en')) {
            $assetBundle->autoGenerate = false;
            $assetBundle->js[] = "ui/i18n/datepicker-{$this->pickerLanguage}.js";
        } else {
            $assetBundle->language = $this->pickerLanguage;
        }
    }
}
