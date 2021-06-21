<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\i18n;

use IntlDateFormatter;
use Yii;
use yii\base\InvalidArgumentException;

/**
 * @inheritdoc
 */
class Formatter extends \yii\i18n\Formatter
{

    /**
     * @inheritdoc
     */
    public $sizeFormatBase = 1000;

    /**
     * @var string the default format string to be used to format a input field [[asDate()|date]].
     * This mostly used in forms (DatePicker).
     * @see dateFormat
     */
    public $dateInputFormat = 'short';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (Yii::$app->params['installed'] && Yii::$app->getModule('admin') !== null && !empty(Yii::$app->getModule('admin')->settings->get('defaultDateInputFormat'))) {
            $this->dateInputFormat = Yii::$app->getModule('admin')->settings->get('defaultDateInputFormat');
        }
    }

    /**
     * Returns the date pattern for the given $locale and $dateType, $timeType.
     *
     * @since 1.2.2
     * @param int $dateType
     * @param int $timeType
     * @param null $locale
     * @return null|string
     */
    public function getDateTimePattern($dateType = IntlDateFormatter::SHORT, $timeType = IntlDateFormatter::SHORT, $locale = null)
    {
        if (extension_loaded('intl')) {
            $locale = empty($locale) ? $this->locale : $locale;
            $formatter = new IntlDateFormatter($locale, $dateType, $timeType, $this->timeZone, $this->calendar);
            return $formatter->getPattern();
        } else {
            return null;
        }
    }

    /**
     * Checks if the time pattern of a given $locale contains a meridiem (AM/PM).
     * If no $locale is provided the Formatter locale will be used.
     *
     * @since 1.2.2
     * @param null $locale
     * @return bool if the locale uses a 12 hour (AM/PM) otherwise a 24 hour time format is used.
     */
    public function isShowMeridiem($locale = null)
    {
        if (extension_loaded('intl')) {
            $pattern = $this->getDateTimePattern(IntlDateFormatter::NONE, IntlDateFormatter::SHORT);
            return strpos($pattern, 'a') !== false;
        }
        return false;
    }

    /**
     * Formats the value as short integer number by removing any decimal and thousand digits without rounding.
     *
     * @param mixed $value the value to be formatted.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result, e.g. 5K, 123M, 42B, 9T, 1Q
     * @throws InvalidArgumentException if the input value is not numeric or the formatting failed.
     */
    public function asShortInteger($value, $options = [], $textOptions = [])
    {
        list($params, $position) = $this->formatNumber($value, null, 2, 1000, $options, $textOptions);
        $params['nFormatted'] = floor((float)$params['nFormatted']);

        switch ($position) {
            case 0:
                return $params['nFormatted'];
            case 1:
                return Yii::t('base', '{nFormatted}K', $params, $this->language); // Thousand
            case 2:
                return Yii::t('base', '{nFormatted}M', $params, $this->language); // Million
            default:
                return Yii::t('base', '{nFormatted}B', $params, $this->language); // Billion
        }
    }
}
