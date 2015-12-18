<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

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
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        $timestamp = $this->parseDateValue($value);
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
            $date->setTimezone(new \DateTimeZone('UTC'));
            $model->$attribute = $date->format($this->convertToFormat);
        }
    }

    /**
     * @inheritdoc
     */
    protected function parseDateValue($value)
    {
        if ($this->isInDbFormat($value)) {
            return strtotime($value);
        }

        return parent::parseDateValue($value);
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
