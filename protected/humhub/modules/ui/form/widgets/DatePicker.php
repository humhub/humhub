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

        /**
         * HUMHUB PATCH: On some ICU builds, a locale's date pattern (used above to build the picker's
         * client-side dateFormat) contains a non-standard space character - e.g. U+202F NARROW NO-BREAK
         * SPACE, as used before the "г." year suffix in `ru` - that does not always match the plain space
         * ICU itself writes into the value actually rendered into the input (see
         * Yii::$app->formatter->asDate() in yii\jui\DatePicker::renderWidget()). IntlDateFormatter::parse()
         * tolerates this on the server, but jQuery UI's own parseDate() matches format literals character
         * for character and does not, so such a mismatch makes the calendar unable to recognize a saved
         * value and silently fall back to today's date when reopened. Normalizing both sides to a plain
         * space keeps them in agreement regardless of which space ICU chose on either side.
         */
        $this->clientOptions['dateFormat'] = $this->normalizeIcuWhitespace($this->clientOptions['dateFormat']);

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
         *
         * Applied unconditionally (not only when a jQuery UI regional file is loaded below): some
         * locales (see LANGUAGEMAPPING, e.g. `uz`, `ht`, `am`) have no bundled jQuery UI translation
         * at all, so pickerLanguage is forced to `en-US` and no regional file is ever registered for
         * them - but the server still validates against the real locale ($language), not `en-US`. Only
         * injecting the real locale's month names here (independent of pickerLanguage/regional file)
         * keeps those locales' Save/re-open working too, at the cost of the day names and Prev/Next/
         * Today labels remaining in English for them, same trade-off already accepted for locales
         * whose regional file is simply missing (e.g. `cy`, `sw`).
         */
        $this->clientOptions += $this->getIntlMonthNames($language);

        if ($this->pickerLanguage !== 'en-US' && $this->pickerLanguage !== 'en') {
            $this->registerLanguageAsset();

            $options = Json::htmlEncode($this->clientOptions);
            $this->pickerLanguage = Html::encode($this->pickerLanguage);
            $this->getView()->registerJs("jQuery('#{$containerID}').datepicker($.extend({}, $.datepicker.regional['{$this->pickerLanguage}'], $options));");
        } else {
            $this->registerClientOptions('datepicker', $containerID);
        }

        $this->registerClientEvents('datepicker', $containerID);

        // HUMHUB PATCH: normalize the same class of special-space characters in the value the server
        // just rendered into the field, so it matches the normalized dateFormat literal above (see the
        // comment where clientOptions['dateFormat'] is normalized). Also transliterate any non-ASCII
        // decimal digits (e.g. U+06F0-U+06F9 Extended Arabic-Indic digits used by `fa`/`fa-IR`, or
        // U+0660-U+0669 Arabic-Indic digits used by `ar`) to plain ASCII 0-9: ICU renders day/year
        // numbers using the locale's native digit script, but jQuery UI's own parseDate()/getNumber()
        // only recognizes ASCII digits, so without this the picker can't recognize a saved value and
        // silently falls back to today's date when reopened - the same class of bug as the whitespace
        // mismatch above, just for digits instead of separators.
        $this->getView()->registerJs('jQuery("#' . $containerID . '").val(function (i, v) {
            if (!v) { return v; }
            v = v.replace(/[\u00A0\u2000-\u200A\u202F\u205F\uFEFF]/g, " ");
            v = v.replace(/[\u0660-\u0669\u06F0-\u06F9]/g, function (ch) {
                var code = ch.codePointAt(0);
                var base = (code >= 0x06F0) ? 0x06F0 : 0x0660;
                return String(code - base);
            });
            return v;
        });');

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

    /**
     * Replaces Unicode space variants that ICU sometimes uses in a locale's date pattern/output (e.g.
     * U+202F NARROW NO-BREAK SPACE, U+00A0 NO-BREAK SPACE) with a plain space, so a jQuery UI dateFormat
     * literal always matches the corresponding character in a server-rendered value, even where ICU
     * itself is inconsistent about which one it picks in a given build (see the patch notes in run()).
     *
     * @param string $value
     * @return string
     */
    private function normalizeIcuWhitespace(string $value): string
    {
        return preg_replace('/[\x{00A0}\x{2000}-\x{200A}\x{202F}\x{205F}\x{FEFF}]/u', ' ', $value);
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
