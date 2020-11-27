<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use Yii;
use yii\validators\DateValidator;

/**
 * Validates (user date format or database format) and converts it to an database date(-time) field
 *
 * @see \yii\validators\DateValidator
 * @author luke
 */
class DbDateValidator extends DateValidator
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
     * @var string attribute name to save converted value to
     */
    public $targetAttribute = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->format) {
            $this->format = Yii::$app->formatter->dateInputFormat;
        }

        if(!$this->timeZone) {
            $this->timeZone = DateHelper::getUserTimeZone(true);
        }

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        // If the date is already in system format, we do not need any further translation or parsing
        if(DateHelper::isInDbFormat($model->$attribute, $this->isDateOnly())) {
            return;
        }

        $timeValue = $this->getTimeValue($model);
        $timestamp = $this->parseDateTimeValue($model->$attribute, $timeValue);

        if ($timestamp === false) {
            $this->addError($model, $attribute, $this->message, []);
        } elseif ($this->min !== null && $timestamp < $this->min) {
            $this->addError($model, $attribute, $this->tooSmall, ['min' => $this->minString]);
        } elseif ($this->max !== null && $timestamp > $this->max) {
            $this->addError($model, $attribute, $this->tooBig, ['max' => $this->maxString]);
        } else {
            // If there is no error, and attribute is not yet in DB Format - convert to DB
            $date = new \DateTime(null, new \DateTimeZone('UTC'));
            $date->setTimestamp($timestamp);

            if ($timeValue) {
                // Convert timestamp to apps timezone
                $date->setTimezone(DateHelper::getSystemTimeZone());
            }

            $targetAttribute = ($this->targetAttribute === null) ? $attribute : $this->targetAttribute;

            if ($this->convertToFormat !== null) {
                $model->$targetAttribute = $date->format($this->convertToFormat);
            }
        }
    }

    /**
     * Parses a date and a time value if timeAttribute is specified.
     *
     * @param string $value
     * @return int|false timestamp in system timezone
     * @throws \Exception
     */
    protected function parseDateTimeValue($value, $timeValue = null)
    {
        // It's already a database datetime / no conversion needed.
        if (DateHelper::isInDbFormat($value)) {
            return strtotime($value);
        }

        $timestamp = $this->parseDateValue($value);

        if ($timestamp !== false && $this->hasTime() && !empty($timeValue)) {
            $timestamp += $this->parseTimeValue($timeValue);
            $timestamp = $this->fixTimestampTimeZone($timestamp, $this->timeZone);
        }

        return $timestamp;
    }

    /**
     * Checks a time attribute name is given, if empty don't handle time
     *
     * @return boolean
     */
    protected function hasTime()
    {
        return !empty($this->timeAttribute);
    }

    /**
     * @return bool checks if the validator should validate date only fields
     */
    protected function isDateOnly()
    {
        return !$this->hasTime();
    }

    /**
     * Returns time value if provided by the model
     *
     * @return string|null time value (e.g. 12:00)
     */
    protected function getTimeValue($model)
    {
        if ($this->hasTime()) {
            $attributeName = $this->timeAttribute;
            return $model->$attributeName ?: null;
        }

        return null;
    }

    /**
     * Parses a date and optionally a time if timeAttribute is specified.
     *
     * Returns false in case the value could not be parsed.
     *
     * @param string $value
     * @return int|false
     * @throws \Exception
     */
    public static function parseDateTime($value, $timeValue = null)
    {
        return (new self())->parseDateTimeValue($value, $timeValue);
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
     * Converts the given timestamp from user (or configured) timezone to a utc timestamp
     *
     * @param int $ts the timestamp
     * @param String $timeZone users timezone
     * @return int
     * @throws \Exception
     */
    protected function fixTimestampTimeZone($ts, $timeZone)
    {
        // Create date string
        $fromDateTime = new \DateTime('@' . $ts);

        // Create date object
        $toDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $fromDateTime->format('Y-m-d H:i:s'), new \DateTimeZone($timeZone));
        $toDateTime->setTimezone(new \DateTimeZone('UTC'));

        return $toDateTime->getTimestamp();
    }
}
