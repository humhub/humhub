<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\widgets;

use humhub\libs\DbDateValidator;
use humhub\widgets\form\DatePicker;
use IntlDateFormatter;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\base\DynamicModel;
use yii\helpers\Json;

/**
 * The jQuery UI datepicker formats/parses the input value with its own bundled month names, while
 * the server validates and re-formats the same value with PHP intl/ICU. Both catalogs must agree
 * for the locale, otherwise a date picked in the UI fails validation (e.g. "Sep" vs "Sept" for en-GB).
 */
class DatePickerTest extends HumHubDbTestCase
{
    public function testAbbreviatedMonthFormatUsesIcuShortMonthNames()
    {
        // en-GB medium = "d MMM y"; jQuery UI's en-GB file says "Sep", ICU/CLDR says "Sept"
        $options = $this->getDatePickerClientOptions('en-GB', 'medium');

        $this->assertSame(self::getIcuMonthNames('en-GB', 'MMM'), $options['monthNamesShort']);
        // The long names are only used for the calendar header here, so jQuery UI's standalone names stay
        $this->assertArrayNotHasKey('monthNames', $options);

        // What the picker writes into the input must be what the server formats ...
        $this->assertStringContainsString($options['monthNamesShort'][8], Yii::$app->formatter->asDate('2005-09-15', 'medium'));

        // ... and what the server accepts on save (jQuery UI pattern "d M yy")
        $model = new DynamicModel(['birthday' => '15 ' . $options['monthNamesShort'][8] . ' 2005']);
        (new DbDateValidator(['format' => 'medium', 'convertToFormat' => 'Y-m-d']))->validateAttribute($model, 'birthday');
        $this->assertEmpty($model->getErrors());
        $this->assertSame('2005-09-15', $model->birthday);
    }

    public function testNumericMonthFormatLeavesMonthNamesUntouched()
    {
        // de short = "dd.MM.yy": the input never contains a month name, nothing to sync
        $options = $this->getDatePickerClientOptions('de', 'short');

        $this->assertArrayNotHasKey('monthNamesShort', $options);
        $this->assertArrayNotHasKey('monthNames', $options);
    }

    public function testFullMonthFormatUsesIcuLongMonthNames()
    {
        // de long = "d. MMMM y"
        $options = $this->getDatePickerClientOptions('de', 'long');

        $this->assertSame(self::getIcuMonthNames('de', 'MMMM'), $options['monthNames']);
        $this->assertArrayNotHasKey('monthNamesShort', $options);
    }

    public function testLocalesWithoutJqueryUiTranslationGetIcuMonthNamesToo()
    {
        // uz has no jQuery UI translation (LANGUAGEMAPPING forces the en-US picker), medium = "d-MMM, y"
        $options = $this->getDatePickerClientOptions('uz', 'medium');

        $this->assertSame(self::getIcuMonthNames('uz', 'MMM'), $options['monthNamesShort']);
        $this->assertArrayNotHasKey('monthNames', $options);
    }

    public function testExplicitClientOptionsAreNotOverridden()
    {
        $custom = ['M1', 'M2', 'M3', 'M4', 'M5', 'M6', 'M7', 'M8', 'M9', 'M10', 'M11', 'M12'];
        $options = $this->getDatePickerClientOptions('en-GB', 'medium', ['monthNamesShort' => $custom]);

        $this->assertSame($custom, $options['monthNamesShort']);
    }

    /**
     * Renders the widget and returns the options object passed to `jQuery(...).datepicker(...)`.
     */
    private function getDatePickerClientOptions(string $language, string $dateFormat, array $clientOptions = []): array
    {
        Yii::$app->setLanguage($language);
        Yii::$app->formatter->locale = $language;
        Yii::$app->view->clear();

        DatePicker::widget([
            'name' => 'date',
            'value' => '2005-09-15',
            'dateFormat' => $dateFormat,
            'clientOptions' => $clientOptions,
        ]);

        foreach (array_merge(...array_values(Yii::$app->view->js)) as $js) {
            // jQuery('#w0').datepicker($.extend({}, $.datepicker.regional['en-GB'], {...}));  or  jQuery('#w0').datepicker({...});
            if (preg_match('/\.datepicker\((?:\$\.extend\(\{\}, \$\.datepicker\.regional\[\'[^\']+\'\], )?(\{.*\})\)?\);$/', $js, $matches)) {
                return Json::decode($matches[1]);
            }
        }

        $this->fail('No datepicker initialisation found in registered JS');
    }

    private static function getIcuMonthNames(string $locale, string $pattern): array
    {
        $formatter = new IntlDateFormatter($locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, 'UTC', IntlDateFormatter::GREGORIAN, $pattern);
        $names = [];
        for ($month = 1; $month <= 12; $month++) {
            $names[] = $formatter->format(gmmktime(0, 0, 0, $month, 15, 2000));
        }

        return $names;
    }
}
