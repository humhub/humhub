<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use Yii;

/**
 * Validates (user date format or database format) and converts it to an database date(-time) field
 *
 * @see \yii\validators\DateValidator
 * @author luke
 */
class DbDateValidator extends \yii\validators\DateValidator
{

    /**
     * Database Field - Validators
     */
    const REGEX_DBFORMAT_DATE = '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/';
    const REGEX_DBFORMAT_DATETIME = '/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/';

    /**
     * @var string the format the value should converted to (database datetime or date format)
     */
    public $convertToFormat = 'Y-m-d H:i:s';

    /**
     * @var string attribute which holds the time in format hh::mm
     */
    public $timeAttribute = '';

    /**
     * @var defines the source time zone which is by default the user time zone, this can be used if a form uses an own
     * timestamp setting.
     */
    public $timeZone;

    /**
     * @var string attribute name to save converted value to 
     */
    public $targetAttribute = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->format === null) {
            $this->format = Yii::$app->formatter->dateInputFormat;
        }

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {

        // If no source timeZone
        if (empty($this->timeZone)) {
            $this->timeZone = (!\Yii::$app->formatter->timeZone) ? \Yii::$app->timeZone : \Yii::$app->formatter->timeZone;
        }

        $timestamp = $this->parseDateTimeValue($model->$attribute, $this->getTimeValue($model));

        if ($timestamp === false) {
            $this->addError($model, $attribute, $this->message, []);
        } elseif ($this->min !== null && $timestamp < $this->min) {
            $this->addError($model, $attribute, $this->tooSmall, ['min' => $this->minString]);
        } elseif ($this->max !== null && $timestamp > $this->max) {
            $this->addError($model, $attribute, $this->tooBig, ['max' => $this->maxString]);
        } elseif (!$this->isInDbFormat($model->$attribute)) {
            // If there is no error, and attribute is not yet in DB Format - convert to DB
            $date = new \DateTime();
            $date->setTimestamp($timestamp);
            if ($this->hasTime()) {
                // Convert timestamp to apps timeZone
                $date->setTimezone(new \DateTimeZone(\Yii::$app->timeZone));
            } else {
                // If we do not need to respect time, set timezone to utc
                // To ensure we're saving 00:00:00 time infos.
                $date->setTimezone(new \DateTimeZone('UTC'));
            }

            $targetAttribute = ($this->targetAttribute === null) ? $attribute : $this->targetAttribute;

            if ($this->convertToFormat !== null) {
                $model->$targetAttribute = $date->format($this->convertToFormat);
            }
        }
    }

    /**
     * Checks a time attribute name is given, if empty don't handle time
     *
     * @return boolean
     */
    protected function hasTime()
    {
        return ($this->timeAttribute != "");
    }

    /**
     * Returns time value
     *
     * @return string time value (e.g. 12:00)
     */
    protected function getTimeValue($model)
    {
        if ($this->hasTime()) {
            $attributeName = $this->timeAttribute;
            return $model->$attributeName;
        }

        return '';
    }

    /**
     * Parses a date and optionally a time if timeAttribute is specified.
     *
     * @param string $value
     * @return int timestamp in utc
     */
    public static function parseDateTime($value, $timeValue = null)
    {
        return (new self())->parseDateTimeValue($value, $timeValue);
    }

    /**
     * Parses a date and optionally a time if timeAttribute is specified.
     *
     * @param string $value
     * @return int timestamp in utc
     */
    protected function parseDateTimeValue($value, $timeValue = "")
    {
        // It's already a database datetime / no conversion needed.
        if ($this->isInDbFormat($value)) {
            return strtotime($value);
        }

        $timestamp = $this->parseDateValue($value);

        if ($this->hasTime() && $timeValue != "") {
            $timestamp += $this->parseTimeValue($timeValue);
            $timestamp = $this->fixTimestampTimeZone($timestamp, $this->timeZone);
        }

        return $timestamp;
    }

    /**
     * Converts the given timestamp from user (or configured) timezone to a utc timestamp
     *
     * @param long $ts the timestamp
     * @param String $timeZone users timezone
     * @return long the timestamp in utc
     */
    protected function fixTimestampTimeZone($ts, $timeZone)
    {
        // Create date string
        $fromDateTime = new \DateTime("@" . $ts);

        // Create date object
        $toDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $fromDateTime->format('Y-m-d H:i:s'), new \DateTimeZone($timeZone));
        $toDateTime->setTimezone(new \DateTimeZone('UTC'));

        return $toDateTime->getTimestamp();
    }

    /**
     * Parses given time value (hh:mm) to seconds
     *
     * @todo Allow more time formats
     * @param string $value
     * @return int time converted to seconds
     */
    protected function parseTimeValue($value)
    {
        return strtotime($value) - strtotime('TODAY');
    }

    /**
     * Checks whether the given value is a db date format or not.
     *
     * @param string $value the date value
     * @return boolean
     */
    protected function isInDbFormat($value)
    {
        return (preg_match(self::REGEX_DBFORMAT_DATE, $value) || preg_match(self::REGEX_DBFORMAT_DATETIME, $value));
    }

}
